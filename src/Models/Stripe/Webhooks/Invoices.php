<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Enums\HttpCode;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Exception;
use JsonException;
use OpenApi\Annotations as OA;
use Stripe\Event;
use Stripe\Invoice;

class Invoices extends AbstractWebhook
{

    /** @var array */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::INVOICE_CREATED,
        Event::INVOICE_PAYMENT_FAILED,
        Event::INVOICE_PAYMENT_ACTION_REQUIRED,
        Event::INVOICE_UPCOMING,
        Event::INVOICE_PAID,
    ];

    /**
     * @OA\Post(
     *     path="/stripe/webhooks/invoices",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe Invoices",
     *     operationId="webhookStripeInvoices",
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
            objectClassName: Invoice::class,
            webhookSecret: $stripe->getInvoicesWebhookSecret()
        );

        $stripe->processInvoiceWebhook($stripeEvent);

        return HttpCode::Created;
    }
}