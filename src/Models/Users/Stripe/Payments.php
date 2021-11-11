<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Users\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use RuntimeException;

class Payments extends AbstractModel
{

    /**
     * @OA\Post(
     *     path="/users/{user_id}/stripe/payments",
     *     tags={"stripe"},
     *     summary="Create a new Stripe payment intent",
     *     operationId="createStripePayment",
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="receiper",
     *                 @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
     *                 @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
     *                 @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
     *             ),
     *             @OA\Property(property="phlowFee",
     *                 @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
     *                 @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
     *                 @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="A new Stripe payment intent created",
     *         @OA\JsonContent(ref="#/components/schemas/stripePayment")
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
     * @param PositionedEncryptedParameter $receiper
     * @param array $payload
     * @return int
     */
    public function post(
        UserServiceInterface $currentUser,
        Stripe $stripe,
        PositionedEncryptedParameter $receiper,
        array $payload
    ): int
    {
        // TODO save receiper email, when such a possibility will be implemented
        $currentUser->load();

        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        $this->document = $stripe->paymentIntent(
            payerId: $currentUser->getId(),
            receiperId: $receiper->getValue(),
            amount: new Amount(
                integer: $payload['receiper']['amount'],
                cents: $payload['receiper']['cents'],
                currency: Currency::from($payload['receiper']['currency'])
            ),
            phlowFee: new Amount(
                integer: $payload['phlowFee']['amount'],
                cents: $payload['phlowFee']['cents'],
                // TODO check if Phlow can accept this currency, read about multicurrency https://stripe.com/docs/connect/currencies
                currency: Currency::from($payload['phlowFee']['currency'])
            ),
            payerEmail: $currentUser->getEmail(),
        );

        return 201;
    }
}