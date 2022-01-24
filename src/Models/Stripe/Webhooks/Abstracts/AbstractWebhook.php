<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use LogicException;
use RuntimeException;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class AbstractWebhook extends AbstractModel
{

    /** @var string[] */
    protected const SUPPORTED_EVENT_TYPES = [];

    /**
     * @param string $objectClassName
     * @param string $webhookSecret
     * @return Event
     */
    protected static function validateEvent(
        string $objectClassName,
        string $webhookSecret
    ): Event
    {
        if (empty(static::SUPPORTED_EVENT_TYPES)) {
            throw new LogicException(message: 'SUPPORTED_EVENT_TYPES class property must be defined in ' . static::class);
        }

        try {
            $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            $event           = Webhook::constructEvent(file_get_contents('php://input'), $signatureHeader, $webhookSecret);
        } catch (UnexpectedValueException $e) {
            throw new RuntimeException(message: 'Webhook failed to parse a request', code: 400, previous: $e);
        } catch (SignatureVerificationException $signatureException) {
            throw new RuntimeException(message: 'Webhook failed to validate a signature', code: 400, previous: $signatureException);
        }

        if (! in_array(needle: $event->type, haystack: static::SUPPORTED_EVENT_TYPES, strict: true)) {
            throw new RuntimeException(message: 'Event ' . $event->type . ' can not be processed in the webhook ' . static::class, code: 422);
        }

        $object = $event->data->object ?? null;
        if (! $object) {
            throw new RuntimeException(message: 'Malformed Stripe event doesn\'t contain a related object', code: 500);
        }

        if ($objectClassName !== get_class($object)) {
            throw new RuntimeException(message: 'Not expected event related object class', code: 500);
        }

        return $event;
    }

}