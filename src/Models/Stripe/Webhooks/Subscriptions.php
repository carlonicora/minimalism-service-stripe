<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use JsonException;
use Stripe\Event;
use Stripe\Subscription;

class Subscriptions extends AbstractWebhook
{

    /** @var array */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::CUSTOMER_SUBSCRIPTION_CREATED,
        Event::CUSTOMER_SUBSCRIPTION_DELETED,
        Event::CUSTOMER_SUBSCRIPTION_UPDATED,
        //Event::CUSTOMER_SUBSCRIPTION_TRIAL_WILL_END,
    ];

    /**
     * @OA\Post(
     *     path="/stripe/webhooks/subscriptions",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe subscriptions",
     *     operationId="webhookStripeSubscriptions",
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
     * @throws Exception
     */
    public function post(
        Stripe $stripe
    ): HttpCode
    {
        $stripeEvent = self::validateEvent(
            objectClassName: Subscription::class,
            webhookSecret: $stripe->getSubscriptionsWebhookSecret()
        );

        $stripe->processSubscriptionWebhook($stripeEvent);

        return HttpCode::Created;
    }
}