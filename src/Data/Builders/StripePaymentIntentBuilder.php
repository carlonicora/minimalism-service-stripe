<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\UsersDataReader;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
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
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripePaymentIntentAttributes"),
 *     @OA\Property(property="links", ref="#/components/schemas/stripePaymentIntentLinks"),
 *     @OA\Property(property="relationships", ref="#/components/schemas/stripePaymentIntentRelationships"),
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentIntentAttributes",
 *     title="Stripe payment intent attributes",
 *     description="Stripe payment intent resource attributes",
 *     @OA\Property(property="stripePaymentIntentId", type="string", format="", nullable=false, minLength="1", maxLength="100", example="pi_asdfas1234234"),
 *     @OA\Property(property="clientSecret", type="string", format="", nullable=false, minLength="1", maxLength="100", example="client_secret_hash"),
 *     @OA\Property(property="amount",
 *         @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
 *         @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
 *         @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
 *     ),
 *     @OA\Property(property="phlowFeeAmount",
 *         @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
 *         @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
 *         @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
 *     ),
 *     @OA\Property(property="status", type="number", format="int32", nullable=false, minimum="0", maximum="3", example="1"),
 *     @OA\Property(property="error", type="string", format="", nullable=true, minLength="1", maxLength="255", example="Error details"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentIntentLinks",
 *     title="Stripe payment intent links",
 *     description="Stripe payment intent resource links",
 *     @OA\Property(property="self", type="string", format="uri", nullable=false, minLength="1", maxLength="100", example="https://api.phlow.com/v2.5/stripe/paymentIntents/pi_3JwjWIJVYb6RvKNf0QzDSKYp")
 * )
 *
 * @OA\Schema(
 *     schema="stripePaymentIntentRelationships",
 *     title="Stripe payment intent relationships",
 *     description="Stripe payment intent resource relationships",
 *     @OA\Property(property="receiper", ref="#/components/schemas/user"),
 *     @OA\Property(property="payer", ref="#/components/schemas/user")
 * )
 */
class StripePaymentIntentBuilder extends AbstractResourceBuilder
{

    /** @var string */
    protected string $type = 'stripePaymentIntent';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        [$amountInt, $amountCents] = Amount::fromCents($data['amount'], $data['currency']);
        [$feeAmountInt, $feeAmountCents] = Amount::fromCents($data['phlowFeeAmount'], $data['currency']);

        $this->response->id = $this->encrypter->encryptId($data['paymentIntentId']);
        $this->response->attributes->add(name: 'stripePaymentIntentId', value: $data['stripePaymentIntentId']);
        $this->response->attributes->add(name: 'clientSecret', value: $data['clientSecret'] ?? null);
        $this->response->attributes->add(name: 'amount', value: ['integer' => $amountInt, 'cents' => $amountCents, 'currency' => $data['currency']]);
        $this->response->attributes->add(name: 'phlowFeeAmount', value: ['integer' => $feeAmountInt, 'cents' => $feeAmountCents, 'currency' => $data['currency']]);
        $this->response->attributes->add(name: 'status', value: $data['status']);
        $this->response->attributes->add(name: 'createdAt', value: $data['createdAt']);
        $this->response->attributes->add(name: 'updatedAt', value: $data['updatedAt']);
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function setLinks(
        array $data
    ): void
    {
        $this->response->links->add(
            new Link(
                name: 'self',
                href: $this->path->getUrl()
                . 'stripe/paymentIntents/'
                . $data['stripePaymentIntentId']
            )
        );
    }

    /**
     * @return array|null
     */
    public function getRelationshipReaders(): ?array
    {
        $response = [];

        /** @see UsersDataReader::byUserId() */
        $response[] = new RelationshipBuilder(
            name: 'payer',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byUserId',
                parameters: ['payerId']
            )
        );

        /** @see UsersDataReader::byUserId() */
        $response[] = new RelationshipBuilder(
            name: 'receiper',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: UsersDataReader::class,
                functionName: 'byUserId',
                parameters: ['receiperId']
            )
        );

        return $response;
    }

}