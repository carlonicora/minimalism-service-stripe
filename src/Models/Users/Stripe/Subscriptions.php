<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Users\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Money\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use CarloNicora\Minimalism\Services\Users\Users;
use Exception;
use OpenApi\Annotations as OA;
use Stripe\Exception\ApiErrorException;

class Subscriptions extends AbstractModel
{

    /**
     * @param Stripe $stripe
     * @param Users $userService
     * @param PositionedEncryptedParameter|null $recieperParam
     * @param int|null $offset
     * @param int|null $length
     * @return HttpCode
     * @throws Exception
     */
    public function get(
        Stripe                        $stripe,
        Users                         $userService,
        ?PositionedEncryptedParameter $recieperParam = null,
        ?int                          $offset = 0,
        ?int                          $length = 10
    ): HttpCode
    {
        if ($recieperParam !== null) {
            $this->document = $stripe-> getRecieperSubscriptions(
                recieperId: $recieperParam->getValue(),
                offset: $offset,
                limit: $length
            );
        } else {
            $userService->load();

            $this->document = $stripe->getPayerSubscriptions(
                payerId: $userService->getId(),
                offset: $offset,
                limit: $length
            );
        }

        $errorCode = current($this->document->errors)?->status;
        return $errorCode ? HttpCode::from($errorCode) : HttpCode::Ok;
    }

    /**
     * @OA\Post(
     *     path="/users/{user_id}/stripe/subscriptions",
     *     tags={"stripe"},
     *     summary="Create a new Stripe subscription to an artist",
     *     operationId="createStripeSubscription",
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="recieper",
     *                 @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
     *                 @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
     *                 @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
     *             ),
     *             @OA\Property(property="phlowFeePercent", type="number", format="int32", nullable=false, minimum="0", maximum="100", example="15"),
     *             @OA\Property(property="frequency", type="string", format="", nullable=false, minLength="4", maxLength="10", example="monthly")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="A new Stripe subscription to an artist created",
     *         @OA\JsonContent(ref="#/components/schemas/stripeSubscription")
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param Stripe $stripe
     * @param UserServiceInterface $currentUser
     * @param PositionedEncryptedParameter $recieperParam
     * @param array $payload
     * @return HttpCode
     * @throws Exception
     * @throws MinimalismException
     */
    public function post(
        Stripe                       $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $recieperParam,
        array                        $payload
    ): HttpCode
    {
        [$amount, $phlowFeePercent, $frequency] = self::processPayload($currentUser, $payload);

        $this->document = $stripe->subscribe(
            payerId: $currentUser->getId(),
            recieperId: $recieperParam->getValue(),
            amount: $amount,
            phlowFeePercent: $phlowFeePercent,
            frequency: $frequency
        );

        $errorCode = current($this->document->errors)?->status;
        return $errorCode ? HttpCode::from($errorCode) : HttpCode::Created;
    }

    /**
     * @param UserServiceInterface $currentUser
     * @param array $payload
     * @return array
     * @throws MinimalismException
     */
    private static function processPayload(
        UserServiceInterface $currentUser,
        array                $payload
    ): array
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access not allowed to guests');
        }

        if (empty($payload['recieper']) || empty($payload['recieper']['amount']) || empty($payload['recieper']['currency'])
            || empty($payload['phlowFeePercent'])
            || empty($payload['frequency'])
            || null === ($frequency = SubscriptionFrequency::from($payload['frequency']))
        ) {
            throw new MinimalismException(status: HttpCode::PreconditionFailed, message: 'Incorrect payload');
        }

        $amount = new Amount(
            integer: $payload['recieper']['amount'],
            cents: $payload['recieper']['cents'],
            currency: Currency::from($payload['recieper']['currency'])
        );

        return [$amount, $payload['phlowFeePercent'], $frequency];
    }

    /**
     * @OA\Delete(
     *     path="/users/{user_id}/subscriptions",
     *     summary="Delete a Stripe subscription to an artist",
     *     tags={"stripe"},
     *     description="Delete a Stripe subscription to an artist",
     *     operationId="deleteStripeSubscription",
     *     @OA\Parameter(ref="#/components/parameters/user_id"),
     *     @OA\Response(response=204, ref="#/components/responses/204"),
     *     @OA\Response(response=404, ref="#/components/responses/404"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param Stripe $stripe
     * @param UserServiceInterface $currentUser
     * @param PositionedEncryptedParameter $recieperParam
     * @return HttpCode
     * @throws ApiErrorException
     * @throws MinimalismException
     */
    public function delete(
        Stripe                       $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $recieperParam,
    ): HttpCode
    {

        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new MinimalismException(status: HttpCode::Forbidden, message: 'Access not allowed to guests');
        }

        $stripe->cancelSubscription(
            recieperId: $recieperParam->getValue(),
            payerId: $currentUser->getId()
        );

        return HttpCode::NoContent;
    }

}