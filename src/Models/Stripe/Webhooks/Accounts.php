<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use JsonException;
use OpenApi\Annotations as OA;
use Stripe\Account;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;

class Accounts extends AbstractWebhook
{
    /** @var Event[] */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::ACCOUNT_UPDATED
    ];

    /**
     * @OA\Post(
     *     path="/stripe/webhooks/accounts",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe accounts",
     *     operationId="webhookStripeAccounts",
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     * @param Stripe $stripe
     * @return HttpCode
     * @throws JsonException
     * @throws ApiErrorException
     * @throws MinimalismException
     */
    public function post(
        Stripe $stripe
    ): HttpCode
    {
        $stripeEvent = self::validateEvent(
            objectClassName: Account::class,
            webhookSecret: $stripe->getAccountWebhookSecret()
        );

        $this->document = $stripe->processAccountsWebhook($stripeEvent);

        return HttpCode::Created;
    }
}