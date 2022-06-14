<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Parameters\PositionedParameter;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Factories\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO\StripePaymentIntentIO;
use Exception;
use OpenApi\Annotations as OA;

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
     * @param PositionedParameter $intent
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        UserServiceInterface $userService,
        PositionedParameter  $intent
    ): HttpCode
    {
        $userService->load();
        if ($userService->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access denied for visitors');
        }

        $paymentIntentIO = $this->objectFactory->create(className: StripePaymentIntentIO::class);
        $paymentIntent   = $paymentIntentIO->byStripePaymentIntentId($intent->getValue());
        if ($userService->getId() !== $paymentIntent->getPayerId()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Payment intent does not belong to the current user');
        }

        $intentsResourceFactory = $this->objectFactory->create(className: StripePaymentIntentsResourceFactory::class);
        $this->document->addResource(
            $intentsResourceFactory->byData($paymentIntent)
        );

        return HttpCode::Ok;
    }

}