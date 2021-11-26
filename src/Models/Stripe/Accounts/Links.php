<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Accounts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\DefaultServiceInterface;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeAccountsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

class Links extends AbstractModel
{

    /**
     * @OA\Post(
     *     path="/stripe/accounts/links",
     *     tags={"stripe"},
     *     summary="Create a new onboardgin link to Stripe",
     *     operationId="createSrtipeLink",
     *     @OA\Response(
     *         response=201,
     *         description="A new link created",
     *         @OA\JsonContent(ref="#/components/schemas/stripeAccountLink")
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param DefaultServiceInterface $defaultService
     * @param UserServiceInterface $currentUser
     * @param LoggerInterface $logger
     * @param Stripe $stripe
     * @param StripeAccountsDataReader $accountsDataReader
     * @return int
     * @throws ApiErrorException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function post(
        DefaultServiceInterface $defaultService,
        UserServiceInterface $currentUser,
        LoggerInterface $logger,
        Stripe $stripe,
        StripeAccountsDataReader $accountsDataReader
    ): int
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        $account = $accountsDataReader->byUserId($currentUser->getId());

        try {
            $this->document = $stripe->createAccountOnboardingLink(
                accountId: $account['stripeAccountId'],
                refreshUrl: $defaultService->getApplicationUrl() . StripeServiceInterface::REFRESH_URL,
                returnUrl: $defaultService->getApplicationUrl() . StripeServiceInterface::RETURN_URL,
            );
        } catch (ApiErrorException $fatalException) {
            // IdempotencyException|ApiConnectionException $tryAgain
            // CardException|PermissionException $additionalInfoToUser
            // OAuthErrorException|UnknownApiErrorException|InvalidRequestException|AuthenticationException
            $logger->critical(
                message: 'API has failed to connect a user account to Stripe',
                domain: 'Stripe',
                context: [
                    'account' => $account['stripeAccountId'],
                    'exception' => [
                        'message' => $fatalException->getMessage(),
                        'file' => $fatalException->getFile(),
                        'line' => $fatalException->getLine(),
                        'trace' => $fatalException->getTraceAsString()
                    ]
                ]
            );

            throw $fatalException;
        }

        return current($this->document->errors)?->status ?? 201;
    }

}