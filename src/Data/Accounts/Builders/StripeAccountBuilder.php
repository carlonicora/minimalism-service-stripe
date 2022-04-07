<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\ResourceBuilder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccount;
use CarloNicora\Minimalism\Services\Stripe\Dictionary\StripeDictionary;
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
    /**
     * @param EncrypterInterface $encrypter
     */
    public function __construct(
        protected EncrypterInterface $encrypter,
    )
    {
    }

    /**
     * @param StripeAccount|ResourceableDataInterface $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        StripeAccount|ResourceableDataInterface $data
    ): ResourceObject
    {
        $encryptedUserId = $this->encrypter->encryptId($data->getId());

        $response = new ResourceObject(
            type: StripeDictionary::StripeAccounts->value,
            id: $encryptedUserId,
        );

        $response->attributes->add(name: 'stripeAccountId', value: $data->getStripeAccountId());
        $response->attributes->add(name: 'email', value: $data->getEmail());
        $response->attributes->add(name: 'status', value: $data->getStatus());
        $response->attributes->add(name: 'payoutsEnabled', value: $data->isPayoutsEnabled());
        $response->attributes->add(name: 'createdAt', value: $data->getCreatedAt());
        $response->attributes->add(name: 'updatedAt', value: $data->getUpdatedAt());

        return $response;
    }

}