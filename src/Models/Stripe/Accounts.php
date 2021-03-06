<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\DefaultServiceInterface;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use Stripe\Exception\ApiErrorException;

class Accounts extends AbstractModel
{
    /**
     * @OA\Post(
     *     path="/stripe/accounts",
     *     tags={"stripe"},
     *     summary="Сonnect user's Phlow account to Stripe",
     *     operationId="createConnectedStripeAccount",
     *     @OA\Response(
     *         response=201,
     *         description="Account connected",
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
     * @param LoggerInterface $logger
     * @param UserServiceInterface $currentUser
     * @param Stripe $stripe
     * @return HttpCode
     * @throws ApiErrorException
     * @throws Exception
     */
    public function post(
        DefaultServiceInterface $defaultService,
        LoggerInterface         $logger,
        UserServiceInterface    $currentUser,
        Stripe                  $stripe
    ): HttpCode
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access not allowed to guests');
        }

        try {
            $account = $stripe->connectAccount(
                userId: $currentUser->getId(),
                email: $currentUser->getEmail(),
            );

            $this->document = $stripe->createAccountOnboardingLink(
                accountId: $account->id,
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
                    'userId' => $currentUser->getId(),
                    'account' => $account?->id ?? null,
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

        $errorCode = current($this->document->errors)?->status;
        return $errorCode ? HttpCode::from($errorCode) : HttpCode::Created;
    }
}