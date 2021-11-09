<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Builders;

use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

/**
 * @OA\Schema(
 *     schema="stripePaymentIdentifier",
 *     title="Stripe payment identifier",
 *     description="Stripe payment resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example="Nz6r5K9lpG4D8jZBmG"),
 *     @OA\Property(property="type", type="string", nullable=false, example="stripePayment")
 * )
 *
 * @OA\Schema(
 *     schema="stripePayment",
 *     title="Stripe payment",
 *     description="Stripe payment resource",
 *     allOf={@OA\Schema(ref="#/components/schemas/stripePaymentIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripePaymentAttributes")
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentAttributes",
 *     title="Stripe payment attributes",
 *     description="Stripe payment resource attributes",
 *     @OA\Property(property="paymentIntentId", type="string", format="", nullable=true, minLength="21", maxLength="21", example="pi_3Jqwy6JVYb6RvKNf1xM0of17"),
 *     @OA\Property(property="clientSecret", type="string", format="", nullable=false, minLength="1", maxLength="100", example="client_secret_hash"),
 *     @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="1", maximum="20000", example="123"),
 *     @OA\Property(property="currency", type="string", format="", nullable=false, minLength="1", maxLength="100", example="usd"),
 *     @OA\Property(property="status", type="number", format="int32", nullable=false, minimum="0", maximum="3", example="1"),
 *     @OA\Property(property="error", type="string", format="", nullable=true, minLength="1", maxLength="255", example="Error details"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 */
class StripePaymentBuilder extends AbstractResourceBuilder
{

    /** @var string  */
    protected string $type = 'stripePayment';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['paymentId']);
        $this->response->attributes->add(name: 'paymentIntentId', value: $data['paymentIntentId']);
        $this->response->attributes->add(name: 'clientSecret', value: $data['clientSecret']);
        $this->response->attributes->add(name: 'amount', value: $data['amount']);
        $this->response->attributes->add(name: 'currency', value: $data['currency']);
        $this->response->attributes->add(name: 'status', value: $data['status']);
        $this->response->attributes->add(name: 'error', value: $data['error']);
        $this->response->attributes->add(name: 'createdAt', value: $data['createdAt']);
        $this->response->attributes->add(name: 'updatedAt', value: $data['updatedAt']);
    }
}