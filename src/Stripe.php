<?php

namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Abstracts\AbstractService;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders\StripeAccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccount;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccountLink;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Factories\StripeAccountsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\IO\StripeAccountIO;
use CarloNicora\Minimalism\Services\Stripe\Data\Invoices\IO\StripeInvoiceIO;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripeCustomer;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripePaymentIntent;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Enums\PaymentIntentStatus;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Factories\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO\StripeCustomerIO;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO\StripePaymentIntentIO;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeProduct;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Factories\StripeSubscriptionsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\IO\StripeProductIO;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\IO\StripeSubscriptionIO;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\DataObjects\StripeEvent;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\IO\StripeEventIO;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Logger\StripeLogger;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Money\Enums\Currency;
use CarloNicora\Minimalism\Services\Users\Users;
use Exception;
use JsonException;
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

    /** @var StripeLogger|null */
    private ?StripeLogger $logger = null;

    /**
     * @param EncrypterInterface $encrypter
     * @param Users $userService
     * @param string $MINIMALISM_SERVICE_STRIPE_API_KEY
     * @param string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_INVOICES
     * @param string $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_SUBSCRIPTIONS
     */
    public function __construct(
        private readonly EncrypterInterface  $encrypter,
        private readonly Users $userService,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_API_KEY,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_CLIENT_ID,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_ACCOUNTS,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_PAYMENTS,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_INVOICES,
        private readonly string              $MINIMALISM_SERVICE_STRIPE_WEBHOOK_SECRET_SUBSCRIPTIONS
    )
    {
    }

    /**
     * @throws Exception
     */
    public function initialise(): void
    {
        $this->logger = $this->objectFactory->create(className: StripeLogger::class);

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

    /**
     * @return string|null
     */
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
            $account                  = $this->client->accounts->retrieve($existingConnectedAccount->getStripeAccountId());
            $status                   = AccountStatus::calculate($account);
            if ($existingConnectedAccount->getStatus() !== $status->value
                || $existingConnectedAccount->isPayoutsEnabled() !== $account->payouts_enabled
            ) {
                $existingConnectedAccount->setStatus($status->value);
                $existingConnectedAccount->setPayoutsEnabled($account->payouts_enabled);

                $accountIO->update($existingConnectedAccount);
            }

            return $account;
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }

            try {
                $accountIO->byUserEmail($email);
                throw new MinimalismException(status: HttpCode::UnprocessableEntity, message: 'A Stripe account with such an email is already connected');
            } catch (MinimalismException $e) {
                if ($e->getStatus() !== HttpCode::NotFound) {
                    throw $e;
                }
            }

            $newAccount = $this->client->accounts->create([
                'type' => self::ACCOUNT_TYPE,
                'email' => $email,
                'metadata' => ['userId' => $userId],
            ]);

            $newLocalAccount = new StripeAccount();
            $newLocalAccount->setId($userId);
            $newLocalAccount->setStripeAccountId($newAccount->id);
            $newLocalAccount->setEmail($email);
            $newLocalAccount->setStatus(AccountStatus::calculate($newAccount)->value);
            $newLocalAccount->setPayoutsEnabled($newAccount->payouts_enabled);

            /** @noinspection UnusedFunctionResultInspection */
            $accountIO->create($newLocalAccount);

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

        $linkDO = new StripeAccountLink();
        $linkDO->setUrl($link->url);
        $linkDO->setCreatedAt($link->created);
        $linkDO->setExpiresAt($link->expires_at);

        $resource = (new StripeAccountLinkBuilder($this->encrypter))->buildResource($linkDO);

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
     * @throws MinimalismException
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
            $recieperLocalAccount->getStatus() !== AccountStatus::Complete->value
            && $recieperLocalAccount->getStatus() !== AccountStatus::Pending->value
            && $recieperLocalAccount->getStatus() !== AccountStatus::RestrictedSoon->value
        ) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Account status of an artist does not allow payments');
        }

        try {
            $paymentMethods = [];
            foreach ($amount->currency()->paymentMethods() as $method) {
                $paymentMethods [] = $method->value;
            }

            $payerCustomerId = $this->getCustomerId(
                payerId: $payerId,
                recieperStripeAccountId: $recieperLocalAccount->getStripeAccountId()
            );

            $stripePaymentIntent = $this->client->paymentIntents->create(
                [
                    'amount' => $amount->inCents(),
                    'application_fee_amount' => $phlowFee->inCents(),
                    'currency' => $amount->currency()->value,
                    'payment_method_types' => $paymentMethods,
                    'receipt_email' => $payerEmail,
                    'customer' => $payerCustomerId,
                    'metadata' => [
                        'payerId' => $payerId,
                        'recieperId' => $recieperId
                    ]
                ],
                ['stripe_account' => $recieperLocalAccount->getStripeAccountId()]
            );

            $user = $this->userService->byId($recieperId);
            if ($user === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'User with such an id does not exists');
            }

            $newLocalPaymentIntent = new StripePaymentIntent();
            $newLocalPaymentIntent->setStripePaymentIntentId($stripePaymentIntent->id);
            $newLocalPaymentIntent->setPayerId($payerId);
            $newLocalPaymentIntent->setPayerEmail($payerEmail);
            $newLocalPaymentIntent->setRecieperId($recieperId);
            $newLocalPaymentIntent->setRecieperAccountId($recieperLocalAccount->getStripeAccountId());
            $newLocalPaymentIntent->setRecieperEmail($user->getEmail());
            $newLocalPaymentIntent->setAmount($amount->inCents());
            $newLocalPaymentIntent->setPhlowFeeAmount($phlowFee->inCents());
            $newLocalPaymentIntent->setCurrency($amount->currency()->value);
            $newLocalPaymentIntent->setStatus(PaymentIntentStatus::from($stripePaymentIntent->status)->value);
            $newLocalPaymentIntent->setStripeInvoiceId($stripePaymentIntent->invoice?->id);

            $paymentIntentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);
            /** @var StripePaymentIntent $createdLocalPaymentIntent */
            $createdLocalPaymentIntent = $paymentIntentIO->create($newLocalPaymentIntent);

            // We can't set client secret before saving to DB. Create method will set it to null
            $createdLocalPaymentIntent->setClientSecret($stripePaymentIntent->client_secret);

            $paymentIntentResourceReader = $this->objectFactory->create(className: StripePaymentIntentsResourceFactory::class);

            $result->addResource(
                $paymentIntentResourceReader->byData($createdLocalPaymentIntent)
            );
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
     * @return string
     * @throws ApiErrorException
     * @throws MinimalismException
     * @throws Exception
     */
    protected function getOrCreatePlatformCustomerId(
        int $userId
    ): string
    {
        $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
        try {
            return $customerIO->byUserId($userId)->getStripeCustomerId();
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }

            $user = $this->userService->byId($userId);
            if ($user === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'User with such an id does not exists');
            }

            $customer = $this->client->customers->create([
                'email' => $user->getEmail(),
                'name' => $user->getUserName(),
                'metadata' => [
                    'userId' => $userId
                ]
            ]);

            $customerIO = $this->objectFactory->create(className: StripeCustomerIO::class);
            $customerDO = new StripeCustomer();
            $customerDO->setId($userId);
            $customerDO->setStripeCustomerId($customer->id);
            $customerDO->setEmail($user->getEmail());

            return $customerIO->create($customerDO)->getStripeCustomerId();
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

        $accountsDataReader   = $this->objectFactory->create(className: StripeAccountIO::class);
        $recieperLocalAccount = $accountsDataReader->byUserId($recieperId);
        if (
            $recieperLocalAccount->getStatus() !== AccountStatus::Complete->value
            && $recieperLocalAccount->getStatus() !== AccountStatus::Pending->value
            && $recieperLocalAccount->getStatus() !== AccountStatus::RestrictedSoon->value
        ) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Account status of an artist does not allow subscriptions');
        }

        $payer = $this->userService->byId($payerId);
        if ($payer === null) {
            throw new MinimalismException(status: HttpCode::NotFound, message: 'Payer with such an id does not exists');
        }

        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        try {
            /** @noinspection UnusedFunctionResultInspection */
            $subscriptionIO->byRecieperAndPayerIds(
                recieperId: $recieperId,
                payerId: $payerId
            );

            throw new MinimalismException(status: HttpCode::Forbidden, message: 'It is forbidden to create second subscriptions. Please, cancel an existing subscription.');
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }

            // 'Subscription does not exists yet' test passed successfully
        }

        try {
            $recieperStripeAccountId = $recieperLocalAccount->getStripeAccountId();

            $product = $this->getOrCreateProduct($recieperId, $recieperStripeAccountId);

            $price = $this->createPrice(
                recieperId: $recieperId,
                recieperStripeAccountId: $recieperStripeAccountId,
                payerId: $payerId,
                stripeProductId: $product->getStripeProductId(),
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

            $localSubscription = new StripeSubscription();
            $localSubscription->setPayerId($payerId);
            $localSubscription->setPayerEmail($payer->getEmail());
            $localSubscription->setRecieperId($recieperId);
            $localSubscription->setRecieperEmail($recieperLocalAccount->getEmail());
            $localSubscription->setStripeSubscriptionId($stripeSubscription->id);
            $localSubscription->setStripeLastInvoiceId($stripeSubscription->latest_invoice->id);
            $localSubscription->setStripeLastPaymentIntentId($stripeSubscription->latest_invoice->payment_intent->id);
            $localSubscription->setStripePriceId($price->id);
            $localSubscription->setProductId($product->getId());
            $localSubscription->setAmount($amount->inCents());
            $localSubscription->setPhlowFeePercent($phlowFeePercent);
            $localSubscription->setStatus($stripeSubscription->status);
            $localSubscription->setCurrency($amount->currency()->value);
            $localSubscription->setFrequency($frequency->value);
            $localSubscription->setCurrentPeriodEnd($stripeSubscription->current_period_end);

            $createdLocalSubscription = $subscriptionIO->create($localSubscription);

            $subscriptionResourceFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
            $localSubscriptionResource   = $subscriptionResourceFactory->byData($createdLocalSubscription);
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

        if (! empty($error)) {
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
     * @return StripeProduct
     * @throws ApiErrorException
     * @throws Exception
     */
    public function getOrCreateProduct(
        int    $recieperId,
        string $recieperStripeAccountId
    ): StripeProduct
    {
        try {
            return $this->objectFactory->create(className: StripeProductIO::class)->byRecieperId($recieperId);
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }

            $user = $this->userService->byId($recieperId);
            if ($user === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'User with such an id does not exists');
            }

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
     * @return StripeProduct
     * @throws ApiErrorException
     * @throws Exception
     */
    protected function createProduct(
        int    $recieperId,
        string $recieperStripeAccountId,
        string $name,
        string $email,
        string $description
    ): StripeProduct
    {
        $user = $this->userService->byId($recieperId);
        if ($user === null) {
            throw new MinimalismException(status: HttpCode::NotFound, message: 'User with such an id does not exists');
        }

        $product = [
            'name' => $name,
            'description' => $description,
            'metadata' => [
                'userId' => $recieperId,
                'email' => $email
            ],
        ];

        if ($avatar = $user->getAttribute(attributeName: 'avatar')) {
            $product['images'] = [$avatar];
        }

        if ($url = $user->getAttribute(attributeName: 'url')) {
            $product['url'] = $url;
        }

        $product = $this->client->products->create(
            $product,
            ['stripe_account' => $recieperStripeAccountId]
        );

        $stripeProduct = new StripeProduct();
        $stripeProduct->setStripeProductId($product->id);
        $stripeProduct->setRecieperId($recieperId);
        $stripeProduct->setName($name);
        $stripeProduct->setDescription($description);

        return $this->objectFactory->create(className: StripeProductIO::class)->create($stripeProduct);
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
        $platformCustomerId = $this->getOrCreatePlatformCustomerId($payerId);

        // TODO check if a customer has a payment method
        $noPaymentMethod = true;
        if ($noPaymentMethod) {
            $user = $this->userService->byId($payerId);
            if ($user === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'User with such an id does not exists');
            }

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
                ['customer' => $platformCustomerId],
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

        $recieper = $this->objectFactory->create(className: StripeAccountIO::class)->byUserId($recieperId);

        /** @noinspection UnusedFunctionResultInspection */
        $this->client->subscriptions->cancel(
            id: $subscription->getStripeSubscriptionId(),
            opts: ['stripe_account' => $recieper->getStripeAccountId()]
        );

        $subscriptionIO->delete($subscription);
    }

    /**
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function getAccountStatuses(int $userId): array
    {
        $accountsDataReader = $this->objectFactory->create(className: StripeAccountIO::class);
        try {
            $account = $accountsDataReader->byUserId($userId);
        } catch (MinimalismException $e) {
            if ($e->getStatus() === HttpCode::NotFound) {
                return [null, null];
            }

            throw $e;
        }

        return [$account->getStatus(), $account->isPayoutsEnabled()];
    }

    /**
     * @param int $userId
     * @return void
     * @throws MinimalismException
     * @throws Exception
     */
    public function deleteStripeDataForUser(
        int $userId
    ): void
    {
        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $subscriptions = $subscriptionIO->byRecieperOrPayerId($userId);
        foreach ($subscriptions as $subscription) {
            $this->cancelSubscription(
                recieperId: $subscription->getRecieperId(),
                payerId: $subscription->getPayerId()
            );
        }
    }

    /**
     * @param int $reciperId
     * @param int $payerId
     * @return Document
     * @throws Exception
     */
    public function getSubscription(
        int $reciperId,
        int $payerId
    ): Document
    {
        $document = new Document();

        $subscriptionIO      = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $subscriptionFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
        try {
            $subscription = $subscriptionIO->byRecieperAndPayerIds(
                recieperId: $reciperId,
                payerId: $payerId
            );

            $document->addResource(
                $subscriptionFactory->byData($subscription)
            );
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }
        }

        return $document;
    }

    /**
     * @param Event $stripeEvent
     * @return Document
     * @throws JsonException
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function processPaymentIntentWebhook(
        Event $stripeEvent
    ): Document
    {
        $stripePaymentIntent = $this->processEvent(
            objectClassName: PaymentIntent::class,
            event: $stripeEvent
        );

        $paymentIntentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);

        try {
            $localPayment = $paymentIntentIO->byStripePaymentIntentId($stripePaymentIntent->id);

            // Check for updates on invoice, which exists in the database (one time payment created by us, or recurring payment created earlier)
            if ($localPayment->getStatus() !== $stripePaymentIntent->status) {
                $localPayment->setStatus(PaymentIntentStatus::from($stripePaymentIntent->status)->value);
                $paymentIntentIO->update($localPayment);
            }
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }

            // A recurring payment intent created by Stripe
            $payerId = $stripePaymentIntent->metadata->offsetGet('payerId');
            $payer   = $this->userService->byId($payerId);
            if ($payer === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'Payer with such an id does not exists');
            }

            $recieperId = $stripePaymentIntent->metadata->offsetGet('recieperId');
            $recieper   = $this->userService->byId($recieperId);
            if ($recieper === null) {
                throw new MinimalismException(status: HttpCode::NotFound, message: 'Recieper with such an id does not exists');
            }

            $stripeAccountIO      = $this->objectFactory->create(className: StripeAccountIO::class);
            $recieperLocalAccount = $stripeAccountIO->byUserId($recieperId);

            $paymentIntent = new StripePaymentIntent();
            $paymentIntent->setStripePaymentIntentId($stripePaymentIntent->id);
            $paymentIntent->setPayerId($payerId);
            $paymentIntent->setPayerEmail($payer->getEmail());
            $paymentIntent->setRecieperId($recieperId);
            $paymentIntent->setRecieperAccountId($recieperLocalAccount->getStripeAccountId());
            $paymentIntent->setRecieperEmail($recieper->getEmail());
            $paymentIntent->setAmount($stripePaymentIntent->amount);
            $paymentIntent->setPhlowFeeAmount($stripePaymentIntent->application_fee_amount);
            $paymentIntent->setCurrency(Currency::from($stripePaymentIntent->currency)->value);
            $paymentIntent->setStatus(PaymentIntentStatus::from($stripePaymentIntent->status)->value);
            $paymentIntent->setStripeInvoiceId($stripePaymentIntent->invoice?->id);

            $localPayment = $paymentIntentIO->create($paymentIntent);
        }

        $resourceFactory = $this->objectFactory->create(className: StripePaymentIntentsResourceFactory::class);
        $paymentResource = $resourceFactory->byData($localPayment);

        $result = new Document();
        $result->addResource($paymentResource);

        return $result;
    }

    /**
     * @param Event $stripeEvent
     * @return void
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

        $subscriptionIO    = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $localSubscription = $subscriptionIO->byStripeSubscriptionId($stripeSubscription->id);

        if ($localSubscription->getStatus() !== $stripeSubscription->status
            || $localSubscription->getStripeLastInvoiceId() !== $stripeSubscription->latest_invoice
            || $localSubscription->getCurrentPeriodEnd() !== $stripeSubscription->current_period_end
        ) {
            $localSubscription->setStatus($stripeSubscription->status);
            $localSubscription->setStripeLastInvoiceId($stripeSubscription->latest_invoice);
            $localSubscription->setCurrentPeriodEnd($stripeSubscription->current_period_end);

            $subscriptionIO->update($localSubscription);
        }
    }

    /**
     * @param Event $stripeEvent
     * @return Document
     * @throws ApiErrorException
     * @throws JsonException
     * @throws Exception
     */
    public function processAccountsWebhook(
        Event $stripeEvent
    ): Document
    {
        $stripeAccount = $this->processEvent(
            objectClassName: Account::class,
            event: $stripeEvent
        );

        $accountIO    = $this->objectFactory->create(className: StripeAccountIO::class);
        $localAccount = $accountIO->byStripeAccountId($stripeAccount->id);
        $userId       = $localAccount->getId();
        $realStatus   = AccountStatus::calculate($stripeAccount);

        if ($localAccount->getStatus() !== $realStatus->value
            || $localAccount->isPayoutsEnabled() !== $stripeAccount->payouts_enabled
        ) {
            $localAccount->setStatus($realStatus->value);
            $localAccount->setPayoutsEnabled($stripeAccount->payouts_enabled);

            $accountIO->update($localAccount);

            if ($stripeAccount->payouts_enabled
                && ($realStatus === AccountStatus::Complete || $realStatus === AccountStatus::Enabled)
            ) {
                /** @noinspection UnusedFunctionResultInspection */
                $this->getOrCreateProduct(
                    recieperId: $userId,
                    recieperStripeAccountId: $stripeAccount->id
                );
            }
        }

        $resourceFactory = $this->objectFactory->create(className: StripeAccountsResourceFactory::class);
        $accountResource = $resourceFactory->byData($localAccount);

        $result = new Document();
        $result->addResource($accountResource);
        return $result;
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
        Event  $event
    ): StripeObject
    {
        // TODO create a separate class for stripe events processing
        $eventIO = $this->objectFactory->create(className: StripeEventIO::class);

        try {
            $existingEvent = $eventIO->byId($event->id);
            if ($existingEvent === $event->id) {
                throw new MinimalismException(status: HttpCode::Ok, message: 'A dublicate webhook was ignored');
            }
        } catch (MinimalismException $e) {
            if ($e->getStatus() !== HttpCode::NotFound) {
                throw $e;
            }
            // A new event should be proccessed, not ignored
        }

        $object = $event->data->object ?? null;
        if (! $object) {
            throw new MinimalismException(status: HttpCode::InternalServerError, message: 'Malformed Stripe event doesn\'t contain a related object');
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
            Invoice::class       => [
                'customer' => $object->customer,
                'customerEmail' => $object->customer_email,
                'status' => $object->status,
                'charge' => $object->charge,
                'paymentIntentId' => $object->payment_intent?->id,
                'subscription' => $object->subscription,
                'total' => $object->total,
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
                'thresholdReason' => $object->threshold_reason ?? null,
            ],
            default              => null
        };

        $localEvent = new StripeEvent();
        $localEvent->setEventId($event->id);
        $localEvent->setType($event->type);
        $localEvent->setCreatedAt($event->created);
        $localEvent->setRelatedObjectId($object->id);
        $localEvent->setDetails($details ? json_encode($details, flags: JSON_THROW_ON_ERROR) : null);

        /** @noinspection UnusedFunctionResultInspection */
        $eventIO->create($localEvent);

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

        $invoiceIO    = $this->objectFactory->create(className: StripeInvoiceIO::class);
        $localInvoice = $invoiceIO->byStripeInvoiceId($stripeInvoice->id);

        if ($localInvoice->getStatus() !== $stripeInvoice->status) {
            $localInvoice->setStatus($stripeInvoice->status);
            $invoiceIO->update($localInvoice);
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