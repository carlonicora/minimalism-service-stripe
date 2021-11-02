<?php
namespace CarloNicora\Minimalism\Services\Stripe;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Error;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Path;
use CarloNicora\Minimalism\Services\Pools;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\AccountLinkBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums\AccountConnectionStatus;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Logger\StripeLogger;
use CarloNicora\Minimalism\Services\Stripe\Traits\StripeLoaders;
use Exception;
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

    private const REFRESH_URL = '/stripe/refresh';
    private const RETURN_URL = '/stripe/return';

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
        private Pools $pools,
        private StripeLogger $logger,
        private Path $path,
        private EncrypterInterface $encrypter,
        private string $MINIMALISM_SERVICE_STRIPE_API_KEY,
        private string $MINIMALISM_SERVICE_STRIPE_CLIENT_ID
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
     * @return Document
     */
    public function connectAccount(
        int $userId,
        string $email,
    ): Document
    {
        $result = new Document();
        try {
            $account = $this->client->accounts->create([
                'type' => 'standard',
                'email' => $email,
                'metadata' => ['userId' => $userId],
            ]);

            $link = $this->client->accountLinks->create([
                'account' => $account->id,
                'refresh_url' => $this->path->getUrl() . self::REFRESH_URL,
                'return_url' =>  $this->path->getUrl() . self::RETURN_URL,
                'type' => self::ACCOUNT_ONBOARDING
            ]);

            $builder = new AccountLinkBuilder(
                path: $this->path,
                encrypter: $this->encrypter
            );
            $builder->setAttributes($link->toArray());
            $resource = $builder->getResourceObject();

            $accountWriter = $this->getAccountsDataWriter();
            $accountWriter->create(
                userId: $userId,
                stripeAccountId: $account->id,
                email: $email,
                connectionStatus: AccountConnectionStatus::Pending
            );

            $result->addResource($resource);
        } catch(CardException $e) {
            // TODO what should we do if a card was declined?
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $error = 'Type is:' . $e->getError()->type . '\n';
            $error .= 'Code is:' . $e->getError()->code . '\n';
            $error .= 'Param is:' . $e->getError()->param . '\n';
        } catch (RateLimitException $e) {
            $error = 'Too many requests made to the Stripe API too quickly';
        } catch (InvalidRequestException $e) {
            $error = 'Invalid parameters were supplied to Stripe\'s API';
        } catch (AuthenticationException $e) {
            $error = 'Authentication with Stripe\'s API failed (maybe you changed API keys recently)';
        } catch (ApiConnectionException $e) {
            $error = 'Network communication with Stripe failed';
        } catch (ApiErrorException $e) {
            $error = 'Stripe has failed to proccess your request. Please, try again later.';
        } catch (Exception $e) {
            $error = 'Internal error';
        }

        if (isset($e) && !empty($error)) {
            $result->addError(new Error($e, httpStatusCode: $e->getHttpStatus(), detail: $error, title: $e->getError()->message));

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