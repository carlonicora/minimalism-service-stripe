<?php
namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\AccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Traits\StripeLoaders;
use Exception;
use RuntimeException;
use Stripe\Account;
use Stripe\BaseStripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class Stripe extends AbstractService implements StripeServiceInterface
{

    use StripeLoaders;

    /** @var string */
    public const VERSION = '2020-08-27';

    private const ACCOUNT_ONBOARDING = 'account_onboarding';

    private const ACCOUNT_TYPE = 'standard';

    /**
     * @var StripeClient
     */
    private StripeClient $client;

    /**
     * @param ObjectFactory $objectFactory
     * @param StripeLogger $logger
     * @param Path $path
     * @param EncrypterInterface $encrypter
     * @param string $MINIMALISM_SERVICE_STRIPE_API_KEY
     * @param string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS
     */
    public function __construct(
        private ObjectFactory      $objectFactory,
        private StripeLogger       $logger,
        private Path               $path,
        private EncrypterInterface $encrypter,
        private string             $MINIMALISM_SERVICE_STRIPE_API_KEY,
        private string             $MINIMALISM_SERVICE_STRIPE_CLIENT_ID,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS

    )
    {
        parent::__construct();

        \Stripe\Stripe::setApiKey($this->MINIMALISM_SERVICE_STRIPE_API_KEY);
        \Stripe\Stripe::setLogger($logger);

        $this->client = new StripeClient([
            'api_key' => $this->MINIMALISM_SERVICE_STRIPE_API_KEY,
            'client_id' => $this->MINIMALISM_SERVICE_STRIPE_CLIENT_ID,
            'stripe_account' => null,
            'stripe_version' => self::VERSION,
            'api_base' => BaseStripeClient::DEFAULT_API_BASE,
            'connect_base' => BaseStripeClient::DEFAULT_CONNECT_BASE,
            'files_base' => BaseStripeClient::DEFAULT_FILES_BASE,
        ]);
    }

    /**
     * @param int $userId
     * @param string $email
     * @return Account
     * @throws ApiErrorException
     * @throws Exception
     */
    public function connectAccount(
        int    $userId,
        string $email,
    ): Account
    {
        try {
            $existingConnectedAccount = $this->getAccountsDataReader()->byUserId($userId);
            $account = $this->client->accounts->retrieve($existingConnectedAccount['stripeAccountId']);
            $status = AccountStatus::calculate($account);
            if ($existingConnectedAccount['status'] !== $status->value
                || (bool)$existingConnectedAccount['payoutsEnabled'] !== $account->payouts_enabled
            ) {
                $this->getAccountsDataWriter()->updateAccountStatuses(
                    userId: $existingConnectedAccount['userId'],
                    status: $status,
                    payoutsEnabled: $account->payouts_enabled
                );
            }

            return $account;
        } catch (RecordNotFoundException) {
            if ($this->getAccountsDataReader()->byUserEmail($email)) {
                throw new RuntimeException(message: 'A Stripe account with such an email is already connected', code: 422);
            }

            $newAccount = $this->client->accounts->create([
                'type' => self::ACCOUNT_TYPE,
                'email' => $email,
                'metadata' => ['userId' => $userId],
            ]);

            $this->getAccountsDataWriter()->create(
                userId: $userId,
                stripeAccountId: $newAccount->id,
                email: $email,
                status: AccountStatus::calculate($newAccount),
                payoutsEnabled: $newAccount->payouts_enabled
            );

            return $newAccount;
        }
    }

    /**
     * @param string $accountId
     * @param string $refreshUrl
     * @param string $returnUrl
     * @return Document
     * @throws ApiErrorException
     * @throws Exception
     */
    public function createAccountOnboardingLink(
        string $accountId,
        string $refreshUrl,
        string $returnUrl
    ): Document
    {
        $result = new Document();

        $link = $this->client->accountLinks->create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => self::ACCOUNT_ONBOARDING
        ]);

        $builder = new AccountLinkBuilder(
            objectFactory: $this->objectFactory,
            path: $this->path,
            encrypter: $this->encrypter
        );
        $builder->setAttributes($link->toArray());
        $resource = $builder->getResourceObject();

        $result->addResource($resource);

        return $result;
    }

    /**
     * @param int $payerId
     * @param int $receiperId
     * @param Amount $amount
     * @param Amount $phlowFee
     * @param string $payerEmail
     * @return Document
     */
    public function paymentIntent(
        int    $payerId,
        int    $receiperId,
        Amount $amount,
        Amount $phlowFee,
        string $payerEmail
    ): Document
    {
        $result = new Document();

        try {
            $receiperLocalAccount = $this->getAccountsDataReader()->byUserId($receiperId);

            $paymentMethods = [];
            foreach ($amount->currency()->paymentMethods() as $method) {
                $paymentMethods [] = $method->value;
            }

            $stripePaymentIntent = $this->client->paymentIntents->create(
                [
                    'amount' => $amount->inCents(),
                    'application_fee_amount' => $phlowFee->inCents(),
                    'currency' => $amount->currency()->value,
                    'payment_method_types' => $paymentMethods,
                    'receipt_email' => $payerEmail,
                    'metadata' => [
                        'payerId' => $payerId,
                        'receiverId' => $receiperId
                    ],
                    'transfer_data' => [
                        'destination' => $receiperLocalAccount['stripeAccountId'],
                    ],
                ],
            );

            $this->getPaymentIntentsDataWriter()->create(
                paymentIntentId: $stripePaymentIntent->id,
                payerId: $payerId,
                payerEmail: $payerEmail,
                receiperId: $receiperId,
                receiperAccountId: $receiperLocalAccount['stripeAccountId'],
                amount: $amount->inCents(),
                phlowFeeAmount: $phlowFee->inCents(),
                currency: $amount->currency()->value,
                status: PaymentIntentStatus::from($stripePaymentIntent->status)
            );

            $localPaymentIntentResource = $this->getPaymentIntentsResourceReader()->byStripePaymentIntentId($stripePaymentIntent->id);
            $localPaymentIntentResource->attributes->update(name: 'clientSecret', value: $stripePaymentIntent->client_secret);

            $result->addResource($localPaymentIntentResource);
        } catch (ApiErrorException $e) {
            $error = 'Stripe has failed to proccess your request. Please, try again later.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if (isset($e) && ! empty($error)) {
            if ($e instanceof ApiErrorException) {
                $status = $e->getHttpStatus();
                $title  = $e->getError()->message;
            } else {
                $status = 500;
                $title  = 'Internal error';
            }
            $result->addError(new Error($e, httpStatusCode: $status, detail: $error, title: $title));

            $this->logger->error(
                message: $error,
                context: [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ]
            );
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function getAccountStatuses(int $userId): array
    {
        $account = $this->getAccountsDataReader()->byUserId($userId);
        return [$account['status'], (bool)$account['payoutsEnabled']];
    }

    /**
     * @return string
     */
    public function getAccountWebhookSecret(): string
    {
        return $this->MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS;
    }

    /**
     * @return string
     */
    public function getPaymentsWebhookSecret(): string
    {
        return $this->MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS;
    }

    public function initialise(): void
    {
    }

    public function destroy(): void
    {
    }

    public static function getBaseInterface(): ?string
    {
        return StripeServiceInterface::class;
    }
}