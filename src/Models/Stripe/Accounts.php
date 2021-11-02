<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use RuntimeException;

class Accounts extends AbstractModel
{

    /**
     * @OA\Post(
     *     path="/stripe/accounts",
     *     tags={"stripe"},
     *     summary="Create a new connected Stripe account for a user",
     *     operationId="createConnectedStripAccount",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             description="A new account resource",
     *             ref="#/components/schemas/account"
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="A new account created",
     *         @OA\JsonContent(ref="#/components/schemas/account")
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param UserServiceInterface $currentUser
     * @param Stripe $stripe
     * @return int
     * @throws Exception
     */
    public function post(
        UserServiceInterface $currentUser,
        Stripe $stripe
    ): int
    {
        $currentUser->load();

        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        // TODO list and analyze accounts connection errors. What should we return to the user in that case?

        $this->document = $stripe->connectAccount(
            userId: $currentUser->getId(),
            email: $currentUser->getEmail()
        );

        return current($this->document->errors)?->status ?? 201;
    }
}