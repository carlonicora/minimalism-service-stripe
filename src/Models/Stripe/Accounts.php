<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Interfaces\DefaultServiceInterface;
use CarloNicora\Minimalism\Interfaces\LoggerInterface;
use CarloNicora\Minimalism\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Interfaces\StripeServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use RuntimeException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\IdempotencyException;

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
     * @return int
     * @throws ApiErrorException
     * @throws Exception
     */
    public function post(
        DefaultServiceInterface $defaultService,
        LoggerInterface $logger,
        UserServiceInterface $currentUser,
        Stripe $stripe
    ): int
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        // TODO list and analyze accounts connection errors. What should we return to the user in that case?

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
//        } catch (CardException $permissionException) {
            // TODO implement
//        } catch (PermissionException $permissionException) {
            //TODO what should we return to a user, how to help him/her to sovle the issue
        } catch (IdempotencyException|ApiConnectionException $tryLaterException) {
            // TODO the fronted should show a 'Try again' message

            $logger->error(
                message: 'Stripe has failed to proccess your request. Please, try again later.',
                domain: 'Stripe',
                context: [
                    'userId' => $currentUser->getId(),
                    'account' => $account?->id ?? null,
                    'exception' => [
                        'message' => $tryLaterException->getMessage(),
                        'file' => $tryLaterException->getFile(),
                        'line' => $tryLaterException->getLine(),
                        'trace' => $tryLaterException->getTraceAsString()
                    ]
                ]
            );
        } catch (ApiErrorException $fatalException) {
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

        return current($this->document->errors)?->status ?? 201;
    }
}