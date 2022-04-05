<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\ResourceBuilder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Dictionary\StripeDictionary;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccountLink;
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
class StripeAccountLinkBuilder extends AbstractResourceBuilder
{

    /**
     * @param EncrypterInterface $encrypter
     */
    public function __construct(
        protected EncrypterInterface $encrypter,
    )
    {
    }

    /**
     * @param StripeAccountLink|ResourceableDataInterface $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        StripeAccountLink|ResourceableDataInterface $data
    ): ResourceObject
    {
        $response = new ResourceObject(
            type: StripeDictionary::SrtipeAccountsLinks->value,
            id: $this->encrypter?->encryptId($data->getId()),
        );
        $response->attributes->add(
            name: 'createdAt',
            value: date(format: 'Y-m-d H:i:s', timestamp: $data->getCreatedAt())
        );
        $response->attributes->add(
            name: 'expiresAt',
            value: date(format: 'Y-m-d H:i:s', timestamp: $data->getExpiresAt())
        );

        $response->attributes->add(name: 'url', value: $data->getUrl());

        return $response;
    }
}