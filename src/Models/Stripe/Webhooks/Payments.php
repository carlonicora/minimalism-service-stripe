<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeEventsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeEventsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripePaymentIntentsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders\StripePaymentIntentsResourceReader;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;
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
     * @param Stripe $stripe
     * @param StripeEventsDataReader $eventsDataReader
     * @param StripeEventsDataWriter $eventsDataWriter
     * @param StripePaymentIntentsResourceReader $paymentsResourceReader
     * @param StripePaymentIntentsDataWriter $paymentsDataWriter
     * @return int
     * @throws RecordNotFoundException|JsonException
     * @throws Exception
     */
    public function post(
        Stripe                             $stripe,
        StripeEventsDataReader             $eventsDataReader,
        StripeEventsDataWriter             $eventsDataWriter,
        StripePaymentIntentsResourceReader $paymentsResourceReader,
        StripePaymentIntentsDataWriter     $paymentsDataWriter
    ): int
    {
        /** @var PaymentIntent $stripePaymentIntent */
        $stripePaymentIntent = $this->processEvent(
            $stripe->getPaymentsWebhookSecret(),
            $eventsDataReader,
            $eventsDataWriter,
        );

        $localPayment = $paymentsResourceReader->byId($stripePaymentIntent->id);

        if ($localPayment->attributes->get('status') !== $stripePaymentIntent->status) {
            $paymentsDataWriter->updateStatus(
                paymentIntentId: $stripePaymentIntent->id,
                status: PaymentIntentStatus::from($stripePaymentIntent->status)
            );
        }

        $this->document->addResource($localPayment);

        return 201;
    }
}