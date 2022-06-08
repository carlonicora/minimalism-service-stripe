<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts;

use CarloNicora\Minimalism\Abstracts\AbstractModel;
use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use LogicException;
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
     * @throws MinimalismException
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
            throw new MinimalismException(status: HttpCode::BadRequest, message: 'Webhook failed to parse a request -' . $e->getMessage());
        } catch (SignatureVerificationException $signatureException) {
            throw new MinimalismException(status: HttpCode::BadRequest, message: 'Webhook failed to validate a signature - ' . $signatureException->getMessage());
        }

        if (! in_array(needle: $event->type, haystack: static::SUPPORTED_EVENT_TYPES, strict: true)) {
            throw new MinimalismException(status: HttpCode::UnprocessableEntity, message: 'Event ' . $event->type . ' can not be processed in the webhook ' . static::class);
        }

        $object = $event->data->object ?? null;
        if (! $object) {
            throw new MinimalismException(status: HttpCode::InternalServerError, message: 'Malformed Stripe event doesn\'t contain a related object');
        }

        if ($objectClassName !== get_class($object)) {
            throw new MinimalismException(status: HttpCode::InternalServerError, message: 'Not expected event related object class');
        }

        return $event;
    }

}