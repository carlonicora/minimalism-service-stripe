<?php

namespace CarloNicora\Minimalism\Services\Stripe\Builders;

use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use Exception;

/**
 * @OA\Schema(
 *     schema="stripeAccountLinkIdentifier",
 *     title="Stripe account link identifier",
 *     description="Stripe account link resource identifier",
 *     @OA\Property(property="type", type="string", nullable=false, example="stripeAccountLink")
 * )
 *
 * @OA\Schema(
 *     schema="stripeAccountLink",
 *     title="Stripe account link",
 *     description="Stripe account link resource",
 *     allOf={@OA\Schema(ref="#/components/schemas/stripeAccountLinkIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripeAccountLinkAttributes")
 * )
 *
 * @OA\Schema(
 *     schema="stripeAccountLinkAttributes",
 *     title="Stripe account link attributes",
 *     description="Stripe account link resource attributes",
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="expiresAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="url", type="string", format="uri", nullable=false, example="https://connect.stripe.com/setup/s/01Xqa1jU8uiQ")
 * )
 */
class AccountLinkBuilder extends AbstractResourceBuilder
{

    /** @var string */
    protected string $type = 'stripeAccountLink';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        $this->response->attributes->add(name: 'createdAt', value: $data['created']);
        $this->response->attributes->add(name: 'expiresAt', value: $data['expires_at']);
        $this->response->attributes->add(name: 'url', value: $data['url']);
    }
}