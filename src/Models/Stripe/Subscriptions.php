<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripeSubscriptionsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeSubscriptionIO;
use Exception;
use RuntimeException;

class Subscriptions extends AbstractModel
{

    /**
     * @OA\Get(
     *     path="/stripe/subscriptions/{subscription_id}",
     *     summary="Get subscription by id",
     *     tags={"stripe"},
     *     description="Get a subscription resource by id",
     *     operationId="getSubscription",
     *     @OA\Parameter(
     *         parameter="subscription_id",
     *         name="subscription_id",
     *         in="path",
     *         required=true,
     *         description="Stripe subscription id",
     *         example="sub_1KKI7q2x6R10KRrhvoOrAbh5",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/stripeSubscription"),
     *             @OA\Property(property="included", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="meta", ref="#/components/schemas/defaultMeta")
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param UserServiceInterface $userService
     * @param PositionedParameter $stripeSubscription
     * @return HttpCode
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function get(
        UserServiceInterface $userService,
        PositionedParameter $stripeSubscription
    ): HttpCode
    {
        $subscriptionIO = $this->objectFactory->create(className: StripeSubscriptionIO::class);
        $subscriptionData = $subscriptionIO->byStripeSubscriptionId($stripeSubscription->getValue());

        $userService->load();
        if ($userService->isVisitor() || $userService->getId() !== $subscriptionData['payerId']) {
            throw new RuntimeException(message: 'Stripe subscription does not belong to the current user', code: 403);
        }

        $subscriptionResourceFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
        $this->document->addResource(
            $subscriptionResourceFactory->byStripeSubscriptionId($stripeSubscription->getValue())
        );

        return HttpCode::Ok;
    }

}