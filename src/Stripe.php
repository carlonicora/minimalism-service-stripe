<?php

namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Factories\ObjectFactory;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Stripe\Builders\AccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripeSubscriptionsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\UserInterface;
use CarloNicora\Minimalism\Services\Stripe\IO\StripePaymentIntentIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeAccountIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeCustomerIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeProductIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeSubscriptionIO;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use Exception;
use RuntimeException;
use Stripe\Account;
use Stripe\BaseStripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;
use Stripe\StripeClient;

class Stripe extends AbstractService implements StripeServiceInterface
{

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
     * @param UserInterface $userInterface
     * @param string $MINIMALISM_SERVICE_STRIPE_API_KEY
     * @param string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS
     */
    public function __construct(
        protected ObjectFactory    $objectFactory,
        private StripeLogger       $logger,
        private Path               $path,
        private EncrypterInterface $encrypter,
        private UserInterface      $userInterface,
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

    public static function getBaseInterface(): ?string
    {
        return StripeServiceInterface::class;
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
        $accountIO = $this->objectFactory->create(className: StripeAccountIO::class);

        try {
            $existingConnectedAccount = $accountIO->byUserId($userId);
            $account                  = $this->client->accounts->retrieve($existingConnectedAccount['stripeAccountId']);
            $status                   = AccountStatus::calculate($account);
            if ($existingConnectedAccount['status'] !== $status->value
                || (bool)$existingConnectedAccount['payoutsEnabled'] !== $account->payouts_enabled
            ) {
                $accountIO->updateAccountStatuses(
                    userId: $existingConnectedAccount['userId'],
                    status: $status,
                    payoutsEnabled: $account->payouts_enabled
                );
            }

            return $account;
        } catch (RecordNotFoundException) {
            if ($accountIO->byUserEmail($email)) {
                throw new RuntimeException(message: 'A Stripe account with such an email is already connected', code: 422);
            }

            $newAccount = $this->client->accounts->create([
                'type' => self::ACCOUNT_TYPE,
                'email' => $email,
                'metadata' => ['userId' => $userId],
            ]);

            $accountIO->create(
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
        string $payerEmail,
    ): Document
    {
        $result = new Document();

        try {
            $accountDataReader    = $this->objectFactory->create(className: StripeAccountIO::class);
            $receiperLocalAccount = $accountDataReader->byUserId($receiperId);

            $paymentMethods = [];
            foreach ($amount->currency()->paymentMethods() as $method) {
                $paymentMethods [] = $method->value;
            }

            $payer = $this->getOrCreateCustomer($payerId);

            $stripePaymentIntent = $this->client->paymentIntents->create(
                [
                    'amount' => $amount->inCents(),
                    'application_fee_amount' => $phlowFee->inCents(),
                    'currency' => $amount->currency()->value,
                    'payment_method_types' => $paymentMethods,
                    'receipt_email' => $payerEmail,
                    'customer' => $payer['stripeCustomerId'],
                    'metadata' => [
                        'payerId' => $payerId,
                        'receiverId' => $receiperId
                    ],
                    'transfer_data' => [
                        'destination' => $receiperLocalAccount['stripeAccountId'],
                    ],
                ],
            );

            $this->userInterface->load($receiperId);

            $paymentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);
            $paymentIO->create(
                paymentIntentId: $stripePaymentIntent->id,
                payerId: $payerId,
                payerEmail: $payerEmail,
                receiperId: $receiperId,
                receiperAccountId: $receiperLocalAccount['stripeAccountId'],
                receiperEmail: $this->userInterface->getEmail(),
                amount: $amount->inCents(),
                phlowFeeAmount: $phlowFee->inCents(),
                currency: $amount->currency()->value,
                status: PaymentIntentStatus::from($stripePaymentIntent->status)
            );

            $paymentIntentResourceReader = $this->objectFactory->create(className: StripePaymentIntentsResourceFactory::class);

            $localPaymentIntentResource = $paymentIntentResourceReader->byStripePaymentIntentId($stripePaymentIntent->id);
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
     * @throws Exception
     */
    protected function getOrCreateCustomer(
        int $userId
    ): array
    {
        $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
        try {
            return $customerIO->byUserId($userId);
        } catch (RecordNotFoundException) {
            $this->userInterface->load($userId);
            $customer = $this->client->customers->create([
                'email' => $this->userInterface->getEmail(),
                'name' => $this->userInterface->getUserName(),
                'metadata' => [
                    'userId' => $userId
                ]
            ]);

            $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
            return $customerIO->create(
                userId: $userId,
                stripeCustomerId: $customer->id,
                email: $this->userInterface->getEmail()
            );
        }
    }

    /**
     * @param int $payerId
     * @param int $receiperId
     * @param Amount $amount
     * @param int $phlowFeePercent
     * @param SubscriptionFrequency $frequency
     * @return Document
     */
    public function subscribe(
        int                   $payerId,
        int                   $receiperId,
        Amount                $amount,
        int                   $phlowFeePercent,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly
    ): Document
    {
        $result = new Document();

        try {
            $product = $this->getOrCreateProduct($receiperId);

            $price = $this->createPrice(
                receiperId: $receiperId,
                payerId: $payerId,
                stripeProductId: $product['stripeProductId'],
                amount: $amount,
                frequency: $frequency
            );

            $customer = $this->getOrCreateCustomer($payerId);

            $stripeSubscription = $this->client->subscriptions->create([
                'customer' => $customer['stripeCustomerId'],
                'items' => [
                    ['price' => $price->id]
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'application_fee_percent' => $phlowFeePercent
            ]);

            $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
            $subscription   = $subscriptionIO->create(
                payerId: $payerId,
                stripeSubscriptionId: $stripeSubscription->id,
                stripePriceId: $price->id,
                stripeProductId: $product['productId'],
                amount: $amount->inCents(),
                phlowFeePercent: $phlowFeePercent,
                currency: $amount->currency(),
                frequency: $frequency
            );

            $subscriptionResourceFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
            $localSubscriptionResource   = $subscriptionResourceFactory->byId($subscription['subscriptionId']);
            $localSubscriptionResource->attributes->update(
                name: 'clientSecret',
                value: $stripeSubscription->latest_invoice->payment_intent->client_secret
            );

            $result->addResource($localSubscriptionResource);

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
     * @param int $artistId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function getOrCreateProduct(
        int $artistId
    ): array
    {
        try {
            return $this->objectFactory->create(className: StripeProductIO::class)->byAuthorId($artistId);
        } catch (RecordNotFoundException) {
            // check if an artist has a connected Stripe account
            $accountsDataReader = $this->objectFactory->create(className: StripeAccountIO::class);
            $accountsDataReader->byUserId($artistId);

            $this->userInterface->load($artistId);
            $description = 'TODO';
            return $this->createProduct(
                userId: $artistId,
                name: $this->userInterface->getUserName(),
                email: $this->userInterface->getEmail(),
                description: $description
            );
        }
    }

    /**
     * @param int $userId
     * @param string $name
     * @param string $email
     * @param string $description
     * @return array
     * @throws ApiErrorException
     * @throws Exception
     */
    protected function createProduct(
        int    $userId,
        string $name,
        string $email,
        string $description
    ): array
    {
        $this->userInterface->load($userId);

        $product = $this->client->products->create([
            'name' => $name,
            'description' => $description,
            'url' => $this->userInterface->getUrl(),
            'images' => [$this->userInterface->getAvatar()],
            'metadata' => [
                'userId' => $userId,
                'email' => $email
            ],
        ]);

        return $this->objectFactory->create(className: StripeProductIO::class)->create(
            stripeProductId: $product->id,
            userId: $userId,
            name: $name,
            description: $description
        );
    }

    /**
     * @param int $receiperId
     * @param int $payerId
     * @param string $stripeProductId
     * @param Amount $amount
     * @param SubscriptionFrequency $frequency
     * @return Price
     * @throws ApiErrorException
     */
    protected function createPrice(
        int                   $receiperId,
        int                   $payerId,
        string                $stripeProductId,
        Amount                $amount,
        SubscriptionFrequency $frequency
    ): Price
    {
        return $this->client->prices->create([
            'product' => $stripeProductId,
            'unit_amount' => $amount->inCents(),
            'currency' => $amount->currency()->value,
            'type' => Price::TYPE_RECURRING,
            'nickname' => $payerId . ' monthly subscription to ' . $receiperId,
            'recurring' => [
                'interval' => $frequency->toStipeConstant(),
                'usage_type' => 'licensed'
            ],
            'metadata' => [
                'from_user_id' => $payerId,
                'to_user_id' => $receiperId
            ],
        ]);
    }

    /**
     * @param int $receiperId
     * @param int $payerId
     * @return void
     * @throws ApiErrorException
     * @throws Exception
     */
    public function cancelSubscription(
        int $receiperId,
        int $payerId,
    ): void
    {
        $subscriptionDataReader = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $subscription           = $subscriptionDataReader->byReceiperAndPayerIds(
            receiperId: $receiperId,
            payerId: $payerId
        );

        $this->client->subscriptions->cancel($subscription['stripeSubscriptionId']);
    }

    /**
     * @param int $userId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function getAccountStatuses(int $userId): array
    {
        $accountsDataReader = $this->objectFactory->create(className: StripeAccountIO::class);
        $account            = $accountsDataReader->byUserId($userId);
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

}