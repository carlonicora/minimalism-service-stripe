<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use JsonException;
use Stripe\Event;
use Stripe\PaymentIntent;

class Payments extends AbstractWebhook
{
    /** @var array */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::PAYMENT_INTENT_AMOUNT_CAPTURABLE_UPDATED,
        Event::PAYMENT_INTENT_CANCELED,
        Event::PAYMENT_INTENT_CREATED,
        Event::PAYMENT_INTENT_PAYMENT_FAILED,
        Event::PAYMENT_INTENT_PROCESSING,
        Event::PAYMENT_INTENT_REQUIRES_ACTION,
        Event::PAYMENT_INTENT_SUCCEEDED,
    ];

    /**
     * @OA\Post(
     *     path="/stripe/webhooks/payments",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe payments",
     *     operationId="webhookStripePayments",
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     *
     * @param Stripe $stripe
     * @return HttpCode
     * @throws JsonException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function post(
        Stripe $stripe
    ): HttpCode
    {
        $stripeEvent = self::validateEvent(
            objectClassName: PaymentIntent::class,
            webhookSecret: $stripe->getPaymentsWebhookSecret()
        );

        $this->document = $stripe->processPaymentIntentWebhook($stripeEvent);

        return HttpCode::Created;
    }
}