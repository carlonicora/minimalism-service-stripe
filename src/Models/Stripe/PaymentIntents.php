<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\IO\StripePaymentIntentIO;
use RuntimeException;

class PaymentIntents extends AbstractModel
{

    /**
     * @OA\Get(
     *     path="/stripe/paymentIntents/{payment_intent_id}",
     *     summary="Get one Stripe payment intent by id",
     *     tags={"stripe"},
     *     description="Get one Stripe payment intent resource by id",
     *     operationId="getStripePaymentIntent",
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
     *             @OA\Property(property="data", ref="#/components/schemas/stripePaymentIntent"),
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
     * @param StripePaymentIntentIO $paymentIntentIO
     * @param StripePaymentIntentsResourceFactory $intentsResourceFactory
     * @param PositionedParameter $intent
     * @return int
     * @throws RecordNotFoundException
     */
    public function get(
        UserServiceInterface                $userService,
        StripePaymentIntentIO               $paymentIntentIO,
        StripePaymentIntentsResourceFactory $intentsResourceFactory,
        PositionedParameter                 $intent
    ): int
    {
        $paymentIntentData = $paymentIntentIO->byStripePaymentIntentId($intent->getValue());

        $userService->load();
        if ($userService->getId() !== $paymentIntentData['payerId']) {
            throw new RuntimeException(message: 'Payment intent does not belong to the current user', code: 403);
        }

        $this->document->addResource(
            $intentsResourceFactory->byStripePaymentIntentId($intent->getValue())
        );

        return 200;
    }

}