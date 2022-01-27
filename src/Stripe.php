<?php

namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Stripe\Builders\AccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\InvoiceStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionStatus;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripeSubscriptionsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\UserLoaderInterface;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeAccountIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeCustomerIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeEventIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeInvoiceIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripePaymentIntentIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeProductIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeSubscriptionIO;
use CarloNicora\Minimalism\Services\Stripe\Logger\StripeLogger;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use Exception;
use JsonException;
use RuntimeException;
use Stripe\Account;
use Stripe\BaseStripeClient;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Price;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Subscription;

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

    /** @var UserLoaderInterface */
    private UserLoaderInterface $userLoader;

    private ?StripeLogger $logger = null;

    /**
     * @param Path $path
     * @param EncrypterInterface $encrypter
     * @param string $MINIMALISM_SERVICE_STRIPE_API_KEY
     * @param string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_INVOICES
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_SUBSCRIPTIONS
     */
    public function __construct(
        private Path               $path,
        private EncrypterInterface $encrypter,
        private string             $MINIMALISM_SERVICE_STRIPE_API_KEY,
        private string             $MINIMALISM_SERVICE_STRIPE_CLIENT_ID,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_INVOICES,
        private string             $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_SUBSCRIPTIONS
    )
    {
    }

    /**
     * @throws Exception
     */
    public function initialise(

    ): void
    {
        $this->logger = $this->objectFactory->create(StripeLogger::class);

        \Stripe\Stripe::setApiKey($this->MINIMALISM_SERVICE_STRIPE_API_KEY);
        \Stripe\Stripe::setLogger($this->logger);

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
     * @param UserLoaderInterface $userService
     * @return void
     */
    public function setUserService(
        UserLoaderInterface $userService
    ): void
    {
        $this->userLoader = $userService;
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
     * @param int $recieperId
     * @param Amount $amount
     * @param Amount $phlowFee
     * @param string $payerEmail
     * @return Document
     * @throws Exception
     */
    public function paymentIntent(
        int    $payerId,
        int    $recieperId,
        Amount $amount,
        Amount $phlowFee,
        string $payerEmail,
    ): Document
    {
        $result = new Document();

        $accountDataReader    = $this->objectFactory->create(className: StripeAccountIO::class);
        $recieperLocalAccount = $accountDataReader->byUserId($recieperId);

        if (
            $recieperLocalAccount['status'] !== AccountStatus::Comlete->value &&
            $recieperLocalAccount['status'] !== AccountStatus::Pending->value &&
            $recieperLocalAccount['status'] !== AccountStatus::RestrictedSoon->value
        ) {
            throw new RuntimeException(message: 'Account status of an artist does not allow payments', code: 403);
        }

        try {
            $paymentMethods = [];
            foreach ($amount->currency()->paymentMethods() as $method) {
                $paymentMethods [] = $method->value;
            }

            $payer = $this->getOrCreatePlatformCustomer($payerId);

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
                        'receiverId' => $recieperId
                    ],
                    'transfer_data' => [
                        'destination' => $recieperLocalAccount['stripeAccountId'],
                    ],
                ],
            );

            $user = $this->userLoader->load($recieperId);

            $paymentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);
            /** @noinspection UnusedFunctionResultInspection */
            $paymentIO->create(
                paymentIntentId: $stripePaymentIntent->id,
                stripeInvoiceId: $stripePaymentIntent->invoice->id,
                payerId: $payerId,
                payerEmail: $payerEmail,
                recieperId: $recieperId,
                recieperAccountId: $recieperLocalAccount['stripeAccountId'],
                recieperEmail: $user->getEmail(),
                amount: $amount->inCents(),
                phlowFeeAmount: $phlowFee->inCents(),
                currency: $amount->currency(),
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
    protected function getOrCreatePlatformCustomer(
        int $userId
    ): array
    {
        $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
        try {
            return $customerIO->byUserId($userId);
        } catch (RecordNotFoundException) {
            $user = $this->userLoader->load($userId);
            $customer = $this->client->customers->create([
                'email' => $user->getEmail(),
                'name' => $user->getUserName(),
                'metadata' => [
                    'userId' => $userId
                ]
            ]);

            $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
            return $customerIO->create(
                userId: $userId,
                stripeCustomerId: $customer->id,
                email: $user->getEmail()
            );
        }
    }

    /**
     * @param int $payerId
     * @param int $recieperId
     * @param Amount $amount
     * @param int $phlowFeePercent
     * @param SubscriptionFrequency $frequency
     * @return Document
     * @throws Exception
     */
    public function subscribe(
        int                   $payerId,
        int                   $recieperId,
        Amount                $amount,
        int                   $phlowFeePercent,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly
    ): Document
    {
        $result = new Document();

        $accountsDataReader = $this->objectFactory->create(className: StripeAccountIO::class);
        $recieper           = $accountsDataReader->byUserId($recieperId);

        $payer = $this->userLoader->load($payerId);

        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        try {
            $existingSubscription = $subscriptionIO->byRecieperAndPayerIds(
                recieperId: $recieperId,
                payerId: $payerId
            );

            throw new RuntimeException(message: 'It is forbidden to create second subscriptions. Please, cancel an existing subscription.', code: 403);
        } catch (RecordNotFoundException) {
            // 'Subscription does not exists yet' test passed successfully
        }

        try {
            $recieperStripeAccountId = $recieper['stripeAccountId'];

            $product = $this->getOrCreateProduct($recieperId, $recieperStripeAccountId);

            $price = $this->createPrice(
                recieperId: $recieperId,
                recieperStripeAccountId: $recieperStripeAccountId,
                payerId: $payerId,
                stripeProductId: $product['stripeProductId'],
                amount: $amount,
                frequency: $frequency
            );

            $customerId = $this->getCustomerId($payerId, $recieperStripeAccountId);

            $stripeSubscription = $this->client->subscriptions->create(
                [
                    'customer' => $customerId,
                    'items' => [
                        ['price' => $price->id]
                    ],
                    'expand' => ['latest_invoice.payment_intent'],
                    'application_fee_percent' => $phlowFeePercent,
                    'payment_behavior' => 'default_incomplete',
                ], ['stripe_account' => $recieperStripeAccountId]
            );

            $subscription = $subscriptionIO->create(
                payerId: $payerId,
                payerEmail: $payer->getEmail(),
                recieperId: $recieperId,
                recieperEmail: $recieper['email'],
                stripeSubscriptionId: $stripeSubscription->id,
                stripeLastInvoiceId: $stripeSubscription->latest_invoice->id,
                stripeLastPaymentIntentId: $stripeSubscription->latest_invoice->payment_intent->id,
                stripePriceId: $price->id,
                productId: $product['productId'],
                amount: $amount->inCents(),
                phlowFeePercent: $phlowFeePercent,
                status: $stripeSubscription->status,
                currency: $amount->currency(),
                frequency: $frequency
            );

            $subscriptionResourceFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
            $localSubscriptionResource   = $subscriptionResourceFactory->byId($subscription['subscriptionId']);
            $localSubscriptionResource->attributes->update(
                name: 'clientSecret',
                value: $stripeSubscription->latest_invoice->payment_intent->client_secret
            );

            $localSubscriptionResource->attributes->update(
                name: 'recieperStripeAccountId',
                value: $recieperStripeAccountId
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
     * @param int $recieperId
     * @param string $recieperStripeAccountId
     * @return array
     * @throws ApiErrorException
     * @throws Exception
     */
    public function getOrCreateProduct(
        int    $recieperId,
        string $recieperStripeAccountId
    ): array
    {
        try {
            return $this->objectFactory->create(className: StripeProductIO::class)->byRecieperId($recieperId);
        } catch (RecordNotFoundException) {
            $user = $this->userLoader->load($recieperId);
            return $this->createProduct(
                recieperId: $recieperId,
                recieperStripeAccountId: $recieperStripeAccountId,
                name: $user->getUserName(),
                email: $user->getEmail(),
                description: 'Monthly payments to ' . $user->getUserName(),
            );
        }
    }

    /**
     * @param int $recieperId
     * @param string $recieperStripeAccountId
     * @param string $name
     * @param string $email
     * @param string $description
     * @return array
     * @throws ApiErrorException
     * @throws Exception
     */
    protected function createProduct(
        int    $recieperId,
        string $recieperStripeAccountId,
        string $name,
        string $email,
        string $description
    ): array
    {
        $user = $this->userLoader->load($recieperId);

        $product = $this->client->products->create(
            [
                'name' => $name,
                'description' => $description,
                'url' => $user->getUrl(),
                'images' => [$user->getAvatar()],
                'metadata' => [
                    'userId' => $recieperId,
                    'email' => $email
                ],
            ],
            ['stripe_account' => $recieperStripeAccountId]
        );

        return $this->objectFactory->create(className: StripeProductIO::class)->create(
            stripeProductId: $product->id,
            recieperId: $recieperId,
            name: $name,
            description: $description
        );
    }

    /**
     * @param int $recieperId
     * @param string $recieperStripeAccountId
     * @param int $payerId
     * @param string $stripeProductId
     * @param Amount $amount
     * @param SubscriptionFrequency $frequency
     * @return Price
     * @throws ApiErrorException
     */
    protected function createPrice(
        int                   $recieperId,
        string                $recieperStripeAccountId,
        int                   $payerId,
        string                $stripeProductId,
        Amount                $amount,
        SubscriptionFrequency $frequency
    ): Price
    {
        return $this->client->prices->create(
            [
                'product' => $stripeProductId,
                'unit_amount' => $amount->inCents(),
                'currency' => $amount->currency()->value,
                'nickname' => $payerId . ' monthly subscription to ' . $recieperId,
                'recurring' => [
                    'interval' => $frequency->toStipeConstant(),
                    'usage_type' => 'licensed'
                ],
                'metadata' => [
                    'from_user_id' => $payerId,
                    'to_user_id' => $recieperId
                ],
            ],
            ['stripe_account' => $recieperStripeAccountId]
        );
    }

    /**
     * @param int $payerId
     * @param string $recieperStripeAccountId
     * @return string
     * @throws ApiErrorException
     * @throws Exception
     */
    protected function getCustomerId(
        int    $payerId,
        string $recieperStripeAccountId
    ): string
    {
        $platformCustomer = $this->getOrCreatePlatformCustomer($payerId);

        // TODO check if a customer has a payment method
        $noPaymentMethod = true;
        if ($noPaymentMethod) {
            $user = $this->userLoader->load($payerId);
            $customer = $this->client->customers->create(
                [
                    'email' => $user->getEmail(),
                    'name' => $user->getUserName(),
                    'metadata' => [
                        'userId' => $payerId
                    ]
                ],
                ['stripe_account' => $recieperStripeAccountId]
            );
        } else {
            $token = $this->client->tokens->create(
                ['customer' => $platformCustomer['stripeCustomerId']],
                ['stripe_account' => $recieperStripeAccountId]
            );

            $customer = $this->client->customers->create(
                ['source' => $token->id],
                ['stripe_account' => $recieperStripeAccountId]
            );
        }

        return $customer->id;
    }

    /**
     * @param int $recieperId
     * @param int $payerId
     * @return void
     * @throws ApiErrorException
     * @throws Exception
     */
    public function cancelSubscription(
        int $recieperId,
        int $payerId,
    ): void
    {
        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $subscription   = $subscriptionIO->byRecieperAndPayerIds(
            recieperId: $recieperId,
            payerId: $payerId
        );

        $recieper = $this->objectFactory->create(StripeAccountIO::class)->byUserId($recieperId);

        /** @noinspection UnusedFunctionResultInspection */
        $this->client->subscriptions->cancel(
            $subscription['stripeSubscriptionId'],
            null,
            ['stripe_account' => $recieper['stripeAccountId']]
        );

        $subscriptionIO->delete($subscription);
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
     * @param Event $stripeEvent
     * @return void
     * @throws JsonException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function processPaymentIntentWebhook(
        Event $stripeEvent
    ): void
    {
        $stripePaymentIntent = $this->processEvent(
            objectClassName: PaymentIntent::class,
            event: $stripeEvent
        );

        $paymentIntentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);

        try {
            $localPayment = $paymentIntentIO->byStripePaymentIntentId($stripePaymentIntent->id);

            // Check for updates on invoice, which exists in the database (one time payment created by us, or recurring payment created earlier)
            if ($localPayment['status'] !== $stripePaymentIntent->status) {
                $paymentIntentIO->updateStatus(
                    paymentIntentId: $stripePaymentIntent->id,
                    status: PaymentIntentStatus::from($stripePaymentIntent->status)
                );
            }
        } catch (RecordNotFoundException) {
            // A recurring payment intent created by Stripe
            $payerId = $stripePaymentIntent->metadata->offsetGet('payerId');
            $payer = $this->userLoader->load($payerId);

            $recieperId = $stripePaymentIntent->metadata->offsetGet('recieperId');
            $reciper = $this->userLoader->load($recieperId);

            $stripeAccountIO = $this->objectFactory->create(className: StripeAccountIO::class);
            $recieperLocalAccount = $stripeAccountIO->byUserId($recieperId);

            /** @noinspection UnusedFunctionResultInspection */
            $paymentIntentIO->create(
                paymentIntentId: $stripePaymentIntent->id,
                stripeInvoiceId: $stripePaymentIntent->invoice->id,
                payerId: $payerId,
                payerEmail: $payer->getEmail(),
                recieperId: $recieperId,
                recieperAccountId: $recieperLocalAccount['stripeAccountId'],
                recieperEmail: $reciper->getEmail(),
                amount: $stripePaymentIntent->amount,
                phlowFeeAmount: $stripePaymentIntent->application_fee_amount,
                currency: Currency::from($stripePaymentIntent->currency),
                status: PaymentIntentStatus::from($stripePaymentIntent->status)
            );
        }
    }

    /**
     * @param Event $stripeEvent
     * @return void
     * @throws RecordNotFoundException
     * @throws JsonException
     * @throws Exception
     */
    public function processSubscriptionWebhook(
        Event $stripeEvent
    ): void
    {
        $stripeSubscription = $this->processEvent(
            objectClassName: Subscription::class,
            event: $stripeEvent
        );

        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $localSubscription = $subscriptionIO->byStripeSubscriptionId($stripeSubscription->id);

        if ($localSubscription['status'] !== $stripeSubscription->status) {
            $subscriptionIO->updateStatus(
                subscriptionId: $stripeSubscription->id,
                status: SubscriptionStatus::from($stripeSubscription->status)
            );
        }
    }

    /**
     * @param Event $stripeEvent
     * @return void
     * @throws JsonException
     * @throws RecordNotFoundException
     * @throws ApiErrorException
     * @throws Exception
     */
    public function processAccountsWebhook(
        Event $stripeEvent
    ): void
    {
        $stripeAccount = $this->processEvent(
            objectClassName: Account::class,
            event: $stripeEvent
        );

        $accountIO = $this->objectFactory->create(className: StripeAccountIO::class);
        $localAccount = $accountIO->byStripeAccountId($stripeAccount->id);
        $userId       = $localAccount['userId'];
        $realStatus   = AccountStatus::calculate($stripeAccount);

        if ($localAccount['status'] !== $realStatus->value
            || (bool)$localAccount['payoutsEnabled'] !== $stripeAccount->payouts_enabled
        ) {
            $accountIO->updateAccountStatuses(
                userId: $userId,
                status: $realStatus,
                payoutsEnabled: $stripeAccount->payouts_enabled
            );

            if ($stripeAccount->payouts_enabled
                && ($realStatus === AccountStatus::Comlete || $realStatus === AccountStatus::Enabled)
            ) {
                /** @noinspection UnusedFunctionResultInspection */
                $this->getOrCreateProduct(
                    recieperId: $userId,
                    recieperStripeAccountId: $stripeAccount->id
                );
            }
        }
    }

    /**
     * @template InstanceOfType
     * @param class-string<InstanceOfType> $objectClassName
     * @param Event $event
     * @return InstanceOfType
     * @throws JsonException
     * @throws Exception
     */
    protected function processEvent(
        string $objectClassName,
        Event $event
    ): StripeObject
    {
        // TODO create a separate class for stripe events processing
        $eventIO = $this->objectFactory->create(className: StripeEventIO::class);

        if (! empty($eventIO->byId($event->id))) {
            throw new RuntimeException(message: 'A dublicate webhook was ignored', code: 200);
        }

        $object = $event->data->object ?? null;
        if (! $object) {
            throw new RuntimeException(message: 'Malformed Stripe event doesn\'t contain a related object', code: 500);
        }

        $details = match ($objectClassName) {
            Account::class       => [
                'status' => AccountStatus::calculate($object)->value,
                'chargesEnabled' => $object->charges_enabled,
                'payoutsEnabled' => $object->payouts_enabled,
            ],
            PaymentIntent::class => [
                'last_payment_error' => $object->last_payment_error,
                'canceled_at' => $object->canceled_at,
                'cancellation_reason' => $object->cancellation_reason
            ],
            Subscription::class  => [
                'status' => $object->status,
                'lastPaymentIntentId' => $object->latest_invoice->payment_intent->id,
                'lastInvoiceId' => $object->latest_invoice->id,
                'current_period_end' => $object->current_period_end,
                'current_period_start' => $object->current_period_start,
                'canceled_at' => $object->canceled_at,
                'ended_at' => $object->ended_at
            ],
            Invoice::class => [
                'status' => $object->status,
                'charge' => $object->charge,
                'paymentIntentId' => $object->payment_intent->id,
                'attempted' => $object->attempted,
                'billingReason' => $object->billing_reason,
                'attemptCount' => $object->attempt_count,
                'amountPaid' => $object->amount_paid,
                'amountRemaining' => $object->amount_remaining,
                'applicationFeeAmount' => $object->application_fee_amount,
                'lastFinalizationError' => $object->last_finalization_error,
                'nextPaymentAttempt' => $object->next_payment_attempt,
                'onBehalfOf' => $object->on_behalf_of,
                'paid' => $object->paid,
                'paidOutOfBand' => $object->paid_out_of_band,
                'thresholdReason' => $object->threshold_reason,
            ],
            default              => null
        };

        /** @noinspection UnusedFunctionResultInspection */
        $eventIO->create(
            eventId: $event->id,
            type: $event->type,
            createdAt: date(format: 'Y-m-d H:i:s', timestamp: $event->created),
            relatedObjectId: $object->id,
            details: $details ? json_encode($details, flags: JSON_THROW_ON_ERROR) : null
        );

        return $object;
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

    /**
     * @param Event $stripeEvent
     * @return void
     * @throws JsonException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function processInvoiceWebhook(
        Event $stripeEvent
    ): void
    {
        $stripeInvoice = $this->processEvent(
            objectClassName: Invoice::class,
            event: $stripeEvent
        );

        $invoiceIO = $this->objectFactory->create(className: StripeInvoiceIO::class);
        $localInvoice = $invoiceIO->byStripeInvoiceId($stripeInvoice->id);

        if ($localInvoice['status'] !== $stripeInvoice->status) {
            $invoiceIO->updateStatus(
                invoiceId: $localInvoice['invoiceId'],
                status: InvoiceStatus::from($stripeInvoice->status)
            );
        }
    }

    /**
     * @return string
     */
    public function getInvoicesWebhookSecret(): string
    {
        return $this->MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_INVOICES;
    }

    /**
     * @return string
     */
    public function getSubscriptionsWebhookSecret(): string
    {
        return $this->MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_SUBSCRIPTIONS;
    }
}