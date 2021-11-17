<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Builders;

use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

/**
 * @OA\Schema(
 *     schema="stripePaymentIntentIdentifier",
 *     title="Stripe payment intent identifier",
 *     description="Stripe payment intent resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example="Nz6r5K9lpG4D8jZBmG"),
 *     @OA\Property(property="type", type="string", nullable=false, example="stripePaymentIntent")
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentIntent",
 *     title="Stripe payment intent",
 *     description="Stripe payment intent resource",
 *     allOf={@OA\Schema(ref="#/components/schemas/stripePaymentIntentIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripePaymentIntentAttributes")
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentIntentAttributes",
 *     title="Stripe payment intent attributes",
 *     description="Stripe payment intent resource attributes",
 *     @OA\Property(property="clientSecret", type="string", format="", nullable=false, minLength="1", maxLength="100", example="client_secret_hash"),
 *     @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="1", maximum="20000", example="123"),
 *     @OA\Property(property="currency", type="string", format="", nullable=false, minLength="1", maxLength="100", example="usd"),
 *     @OA\Property(property="status", type="number", format="int32", nullable=false, minimum="0", maximum="3", example="1"),
 *     @OA\Property(property="error", type="string", format="", nullable=true, minLength="1", maxLength="255", example="Error details"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 */
class StripePaymentIntentBuilder extends AbstractResourceBuilder
{

    /** @var string  */
    protected string $type = 'stripePaymentIntent';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $data['paymentIntentId'];
        $this->response->attributes->add(name: 'clientSecret', value: $data['clientSecret'] ?? null);
        $this->response->attributes->add(name: 'amount', value: $data['amount']);
        $this->response->attributes->add(name: 'currency', value: $data['currency']);
        $this->response->attributes->add(name: 'status', value: $data['status']);
        $this->response->attributes->add(name: 'error', value: $data['error']);
        $this->response->attributes->add(name: 'createdAt', value: $data['createdAt']);
        $this->response->attributes->add(name: 'updatedAt', value: $data['updatedAt']);
    }
}