<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Users\Stripe;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Interfaces\Encrypter\Parameters\PositionedEncryptedParameter;
use CarloNicora\Minimalism\Interfaces\User\Interfaces\UserServiceInterface;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use RuntimeException;
use Stripe\Exception\ApiErrorException;

class Subscriptions extends AbstractModel
{

    /**
     * @param Stripe $stripe
     * @param UserServiceInterface $currentUser
     * @param PositionedEncryptedParameter $recieper
     * @return HttpCode
     * @throws RecordNotFoundException
     */
    public function get(
        Stripe $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $recieper,
    ): HttpCode
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        $this->document = $stripe->getSubscription(
            reciperId: $recieper->getValue(),
            payerId: $currentUser->getId()
        );

        return HttpCode::Ok;
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
     * @param PositionedEncryptedParameter $author
     * @param array $payload
     * @return HttpCode
     * @throws Exception
     */
    public function post(
        Stripe                       $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $author,
        array                        $payload
    ): HttpCode
    {
        [$amount, $phlowFeePercent, $frequency] = self::processPayload($currentUser, $payload);

        $this->document = $stripe->subscribe(
            payerId: $currentUser->getId(),
            recieperId: $author->getValue(),
            amount: $amount,
            phlowFeePercent: $phlowFeePercent,
            frequency: $frequency
        );

        $errorCode = current($this->document->errors)?->status;
        return $errorCode? HttpCode::from($errorCode) : HttpCode::Created;
    }

    /**
     * @param UserServiceInterface $currentUser
     * @param array $payload
     * @return array
     */
    private static function processPayload(
        UserServiceInterface $currentUser,
        array                $payload
    ): array
    {
        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        if (empty($payload['recieper']) || empty($payload['recieper']['amount']) || empty($payload['recieper']['currency']) ||
            empty($payload['phlowFeePercent']) ||
            empty($payload['frequency']) || null === ($frequency = SubscriptionFrequency::from($payload['frequency']))
        ) {
            throw new RuntimeException(message: 'Incorrect payload', code: 412);
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
     * @param PositionedEncryptedParameter $author
     * @return HttpCode
     * @throws ApiErrorException
     */
    public function delete(
        Stripe                       $stripe,
        UserServiceInterface         $currentUser,
        PositionedEncryptedParameter $author,
    ): HttpCode
    {

        $currentUser->load();
        if ($currentUser->isVisitor()) {
            throw new RuntimeException(message: 'Access not allowed to guests', code: 403);
        }

        $stripe->cancelSubscription(
            recieperId: $author->getValue(),
            payerId: $currentUser->getId()
        );

        return HttpCode::NoContent;
    }
}