<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\PaymentIntents;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripeSubscriptionsResourceFactory;
use Exception;

class Subscriptions extends AbstractModel
{

    /**
     * @OA\Get(
     *     path="/stripe/paymentIntents/{payment_intent_id}/subscriptions",
     *     summary="Get subscription by payment intent id",
     *     tags={"stripe"},
     *     description="Get a subscription with the first payment intent",
     *     operationId="getSubscriptionByPaymentIntentId",
     *     @OA\Parameter(
     *         parameter="payment_intent_id",
     *         name="payment_intent_id",
     *         in="path",
     *         required=true,
     *         description="Stripe payment intent id",
     *         example="pi_wZaN92gl7WlRmDWrKp",
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
     * @param PositionedParameter $stripePaymentIntentId
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        PositionedParameter $stripePaymentIntentId
    ): HttpCode
    {

        $subscriptionResourceFactory = $this->objectFactory->create(className: StripeSubscriptionsResourceFactory::class);
        $this->document->addResource(
            $subscriptionResourceFactory->byStripeLastPaymentIntentId($stripePaymentIntentId->getValue())
        );

        return HttpCode::Ok;
    }

}