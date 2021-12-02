<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeEventsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeEventsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use JsonException;
use LogicException;
use RuntimeException;
use Stripe\Account;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeObject;
use Stripe\Webhook;
use UnexpectedValueException;

class AbstractWebhook extends AbstractModel
{

    /** @var Event[] */
    protected const SUPPORTED_EVENT_TYPES = [];

    /**
     * @param string $webhookSecret
     * @param StripeEventsDataReader $eventsDataReader
     * @param StripeEventsDataWriter $eventsDataWriter
     * @return Event
     * @throws JsonException
     */
    protected static function processEvent(
        string                 $webhookSecret,
        StripeEventsDataReader $eventsDataReader,
        StripeEventsDataWriter $eventsDataWriter,
    ): StripeObject
    {
        if (empty(static::SUPPORTED_EVENT_TYPES)) {
            throw new LogicException(message: 'SUPPORTED_EVENT_TYPES class property must be defined in ' . static::class);
        }

        try {
            $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event = Webhook::constructEvent(file_get_contents('php://input'), $signatureHeader, $webhookSecret);
        } catch (UnexpectedValueException $e) {
            throw new RuntimeException(message: 'Webhook failed to parse a request', code: 400, previous: $e);
        } catch (SignatureVerificationException $signatureException) {
            throw new RuntimeException(message: 'Webhook failed to validate a signature', code: 400, previous: $signatureException);
        }

        if (! in_array(needle: $event->type, haystack: static::SUPPORTED_EVENT_TYPES, strict: true)) {
            throw new RuntimeException(message: 'Event ' . $event->type . ' can not be processed in the webhook ' . static::class, code: 422);
        }

        if (! empty($eventsDataReader->byId($event->id))) {
            throw new RuntimeException(message: 'A dublicate webhook was ignored', code: 200);
        }

        $object = $event->data->object ?? null;
        if (! $object) {
            throw new RuntimeException(message: 'Malformed Stripe event doesn\'t contain a related object', code: 500);
        }

        $details = match (get_class($object)) {
            Account::class => ['status' => AccountStatus::calculate($object)->value],
            PaymentIntent::class => [
                'last_payment_error' => $object->last_payment_error,
                'canceled_at' => $object->canceled_at,
                'cancellation_reason' => $object->cancellation_reason
            ],
            default => null
        };

        $eventsDataWriter->create(
            eventId: $event->id,
            type: $event->type,
            createdAt: date(format: 'Y-m-d H:i:s', timestamp: $event->created),
            relatedObjectId: $object->id,
            details: $details ? json_encode($details, flags: JSON_THROW_ON_ERROR) : null
        );

        return $object;
    }

}