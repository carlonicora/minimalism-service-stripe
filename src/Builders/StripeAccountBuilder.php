<?php

namespace CarloNicora\Minimalism\Services\Stripe\Builders;

use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

/**
 * @OA\Schema(
 *     schema="stripeAccountIdentifier",
 *     title="Stripe account identifier",
 *     description="Stripe account resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example=""),
 *     @OA\Property(property="type", type="string", nullable=false, example="stripeAccount")
 * )
 *
 * @OA\Schema(
 *     schema="stripeAccount",
 *     title="Stripe account",
 *     description="Stripe account resource",
 *     allOf={@OA\Schema(ref="#/components/schemas/stripeAccountIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripeAccountAttributes")
 * )
 *
 * @OA\Schema(
 *     schema="stripeAccountAttributes",
 *     title="Stripe account attributes",
 *     description="Stripe account resource attributes",
 *     @OA\Property(property="stripeAccountId", type="string", format="", nullable=false, minLength="1", maxLength="100", example="acc_sa23ksdoi342309sd"),
 *     @OA\Property(property="email", type="string", format="email", nullable=false, minLength="1", maxLength="100", example="email@phlow.com"),
 *     @OA\Property(property="status", type="string", format="", nullable=false, minLength="1", maxLength="100", example="pending"),
 *     @OA\Property(property="payoutsEnabled", type="boolean", example=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 */
class StripeAccountBuilder extends AbstractResourceBuilder
{

    /** @var string */
    protected string $type = 'stripeAccount';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->id = $this->encrypter->encryptId($data['userId']);
        $this->response->attributes->add(name: 'stripeAccountId', value: $data['stripeAccountId']);
        $this->response->attributes->add(name: 'email', value: $data['email']);
        $this->response->attributes->add(name: 'status', value: $data['status']);
        $this->response->attributes->add(name: 'payoutsEnabled', value: $data['payoutsEnabled']);
        $this->response->attributes->add(name: 'createdAt', value: $data['createdAt']);
        $this->response->attributes->add(name: 'updatedAt', value: $data['updatedAt']);
    }
}