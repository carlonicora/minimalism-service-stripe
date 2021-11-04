<?php
namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Pools;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\AccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums\AccountConnectionStatus;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums\PaymentStatus;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Logger\StripeLogger;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Traits\StripeLoaders;
use Exception;
use Stripe\Account;
use Stripe\BaseStripeClient;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\OAuth\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Stripe\StripeClient;

class Stripe implements StripeServiceInterface
{

    use StripeLoaders;

    /** @var string */
    public const VERSION = '2020-08-27';

    private const ACCOUNT_ONBOARDING = 'account_onboarding';

    /**
     * @var StripeClient
     */
    private StripeClient $client;

    /**
     * @param Pools $pools
     * @param StripeLogger $logger
     * @param Path $path
     * @param EncrypterInterface $encrypter
     * @param string $MINIMALISM_SERVICE_STRIPE_API_KEY
     * @param string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
     */
    public function __construct(
        private Pools              $pools,
        private StripeLogger       $logger,
        private Path               $path,
        private EncrypterInterface $encrypter,
        private string             $MINIMALISM_SERVICE_STRIPE_API_KEY,
        private string             $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
    )
    {
        \Stripe\Stripe::setApiKey($this->MINIMALISM_SERVICE_STRIPE_API_KEY);
        \Stripe\Stripe::setLogger($logger);

        $this->client = new StripeClient([
            'api_key' => $this->MINIMALISM_SERVICE_STRIPE_API_KEY,
            'client_id' => $this->MINIMALISM_SERVICE_STRIPE_CLIENT_ID,
            //TODO should we set our own stripe account here? I don't think so, but will dig deeper
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
            return $this->client->accounts->retrieve($existingConnectedAccount['stripeAccountId']);
        } catch (RecordNotFoundException) {
            $newAccount = $this->client->accounts->create([
                'type' => 'standard',
                'email' => $email,
                'metadata' => ['userId' => $userId],
            ]);

            $this->getAccountsDataWriter()->create(
                userId: $userId,
                stripeAccountId: $newAccount->id,
                email: $email,
                connectionStatus: AccountConnectionStatus::Pending
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
     * @param string $receiptEmail
     * @return Document
     */
    public function paymentIntent(
        int    $payerId,
        int    $receiperId,
        Amount $amount,
        Amount $phlowFee,
        string $receiptEmail
    ): Document
    {
        $result = new Document();

        // TODO test idempotency
        // TODO test different currencies, check fees conversions
        // TODO what if one part of the code below failed? Show we rollback the 'transaction'?
        try {
            $paymentsWriter = $this->getPaymentsDataWriter();
            $payment        = $paymentsWriter->create(
                payerId: $payerId,
                receiperId: $receiperId,
                amount: $amount->inCents(),
                phlowFeeAmount: $phlowFee->inCents(),
                currency: $amount->currency()->value,
                status: PaymentStatus::Created
            );

            $accountsReader        = $this->getAccountsDataReader();
            $receiperStripeAccount = $accountsReader->byUserId($receiperId);

            $paymentMethods = [];
            foreach ($amount->currency()->paymentMethods() as $method) {
                $paymentMethods [] = $method->value;
            }

            $paymentIntent = $this->client->paymentIntents->create(
                [
                    'amount' => $amount->inCents(),
                    'application_fee_amount' => $phlowFee->inCents(),
                    'currency' => $amount->currency()->value,
                    'payment_method_types' => $paymentMethods,
                    'receipt_email' => $receiptEmail,
                    'metadata' => [
                        'paymentId' => $payment['paymentId'],
                        'payerId' => $payerId,
                        'receiverId' => $receiperId
                    ],
                    'transfer_data' => [
                        'destination' => $receiperStripeAccount['stripeAccountId'],
                    ],
                    // TODO check how statement_descriptor works. Should we add an author's name to a payment details (22 chars limit)?
                    // Should we allow a user to choose a payment type on the front end?
                    // TODO check how setup_future_usage works. It remembers, which payment type a user has chosen the last time.
                ],
            // TODO what are the benefit for a payer if he/she connect his/her account?
//                [
//                    'stripe_account' => $payerStripeAccount['stripeAccountId']
//                ]
            );

            $paymentsWriter->updatePaymentStatusAndIntentId(
                paymentId: $payment['paymentId'],
                status: PaymentStatus::Sent,
                paymentIntentId: $paymentIntent->id
            );

            $resourceReader  = $this->getPaymentsResourceReader();
            $paymentResource = $resourceReader->byId($payment['paymentId']);
            $paymentResource->attributes->update(name: 'clientSecret', value: $paymentIntent->client_secret);

            $result->addResource($paymentResource);
        } catch (CardException $e) {
            // TODO what should we do if a card was declined?
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $error = 'Type is:' . $e->getError()->type . '\n';
            $error .= 'Code is:' . $e->getError()->code . '\n';
            $error .= 'Param is:' . $e->getError()->param . '\n';
        } catch (InvalidRequestException $e) {
            $error = 'Invalid parameters were supplied to Stripe\'s API';
        } catch (AuthenticationException $e) {
            $error = 'Authentication with Stripe\'s API failed (maybe you changed API keys recently)';
        } catch (RateLimitException $e) {
            $error = 'Too many requests made to the Stripe API too quickly';
        } catch (ApiConnectionException $e) {
            $error = 'Network communication with Stripe failed';
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

    public function initialise(): void
    {
        // TODO: Implement initialise() method.
    }

    public function destroy(): void
    {
        // TODO: Implement destroy() method.
    }
}