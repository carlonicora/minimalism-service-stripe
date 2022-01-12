<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripePaymentIntentsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\IO\StripePaymentIntentIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeEventIO;
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
     *     path="/webhooks/payments",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe payments",
     *     operationId="webhookStripePayments",
     *     @OA\Response(
     *         response=201,
     *         @OA\JsonContent(ref="#/components/schemas/stripePaymentIntent")
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
     * @param StripeEventIO $eventIO
     * @param StripePaymentIntentsResourceFactory $paymentResourceFactory
     * @param StripePaymentIntentIO $paymentIntentIO
     * @return int
     * @throws JsonException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function post(
        Stripe                              $stripe,
        StripeEventIO                       $eventIO,
        StripePaymentIntentsResourceFactory $paymentResourceFactory,
        StripePaymentIntentIO $paymentIntentIO
    ): int
    {
        /** @var PaymentIntent $stripePaymentIntent */
        $stripePaymentIntent = self::processEvent(
            $stripe->getPaymentsWebhookSecret(),
            $eventIO
        );

        $localPayment = $paymentResourceFactory->byStripePaymentIntentId($stripePaymentIntent->id);

        if ($localPayment->attributes->get('status') !== $stripePaymentIntent->status) {
            $paymentIntentIO->updateStatus(
                paymentIntentId: $stripePaymentIntent->id,
                status: PaymentIntentStatus::from($stripePaymentIntent->status)
            );
        }

        $this->document->addResource($localPayment);

        return 201;
    }
}