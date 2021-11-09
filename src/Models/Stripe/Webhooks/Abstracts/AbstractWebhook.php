<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeEventsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeEventsDataWriter;
use LogicException;
use RuntimeException;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
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
     */
    protected function processEvent(
        string $webhookSecret,
        StripeEventsDataReader $eventsDataReader,
        StripeEventsDataWriter $eventsDataWriter,
    ): Event
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
            throw new RuntimeException(message: 'A dublicate webhook was ignored', code: 400);
        }

        $eventsDataWriter->create($event);

        return $event;
    }

}