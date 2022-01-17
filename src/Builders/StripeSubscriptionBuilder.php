<?php

namespace CarloNicora\Minimalism\Services\Stripe\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Builder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\Builder\Objects\RelationshipBuilder;
use CarloNicora\Minimalism\Services\Stripe\IO\UserIO;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use Exception;

/**
 * @OA\Schema(
 *     schema="stripeSubscriptionIdentifier",
 *     title="Stripe subscription identifier",
 *     description="Stripe subscription resource identifier",
 *     @OA\Property(property="id", type="string", nullable=false, minLength=18, maxLength=18, example="Nz6r5K9lpG4D8jZBmG"),
 *     @OA\Property(property="type", type="string", nullable=false, example="stripeSubscription")
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscription",
 *     title="Stripe subscription",
 *     description="Stripe subscription resource",
 *     allOf={@OA\Schema(ref="#/components/schemas/stripeSubscriptionIdentifier")},
 *     @OA\Property(property="attributes", ref="#/components/schemas/stripeSubscriptionAttributes"),
 *     @OA\Property(property="links", ref="#/components/schemas/stripeSubscriptionLinks"),
 *     @OA\Property(property="relationships", ref="#/components/schemas/stripeSubscriptionRelationships"),
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscriptionAttributes",
 *     title="Stripe payment intent attributes",
 *     description="Stripe payment intent resource attributes",
 *     @OA\Property(property="stripeSubscriptionId", type="string", format="", nullable=false, minLength="1", maxLength="100", example="pi_asdfas1234234"),
 *     @OA\Property(property="stripePriceId", type="string", format="", nullable=false, minLength="1", maxLength="100", example="price_1KIx26JVYb6RvKNfnqx0nFMz"),
 *     @OA\Property(property="clientSecret", type="string", format="", nullable=false, minLength="1", maxLength="100", example="client_secret_hash"),
 *     @OA\Property(property="receiper",
 *         @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
 *         @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
 *         @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
 *     ),
 *     @OA\Property(property="phlowFeePercent", type="number", format="int32", nullable=false, minimum="0", maximum="100", example="15"),
 *     @OA\Property(property="status", type="number", format="int32", nullable=false, minimum="0", maximum="3", example="1"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscriptionLinks",
 *     title="Stripe payment intent links",
 *     description="Stripe payment intent resource links",
 *     @OA\Property(property="self", type="string", format="uri", nullable=false, minLength="1", maxLength="100", example="https://api.phlow.com/v2.5/stripe/paymentIntents/pi_3JwjWIJVYb6RvKNf0QzDSKYp")
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscriptionRelationships",
 *     title="Stripe payment intent relationships",
 *     description="Stripe payment intent resource relationships",
 *     @OA\Property(property="receiper", ref="#/components/schemas/user"),
 *     @OA\Property(property="payer", ref="#/components/schemas/user")
 * )
 */
class StripeSubscriptionBuilder extends AbstractResourceBuilder
{

    /** @var string */
    protected string $type = 'stripeSubscriptions';

    /**
     * @param array $data
     * @throws Exception
     */
    public function setAttributes(
        array $data
    ): void
    {
        [$amountInt, $amountCents] = Amount::fromCents($data['amount'], $data['currency']);

        $this->response->id = $this->encrypter->encryptId($data['subscriptionId']);
        $this->response->attributes->add(name: 'stripeSubscriptionId', value: $data['stripeSubscriptionId']);
        $this->response->attributes->add(name: 'stripePriceId', value: $data['stripePriceId']);
        $this->response->attributes->add(name: 'clientSecret', value: $data['clientSecret'] ?? null);
        $this->response->attributes->add(name: 'frequency', value: $data['frequency'] ?? null);
        $this->response->attributes->add(name: 'receiper', value: ['amount' => $amountInt, 'cents' => $amountCents, 'currency' => $data['currency']]);
        $this->response->attributes->add(name: 'phlowFeePercent', value: $data['phlowFeePercent']);
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
                . 'stripe/subscriptions/'
                . $this->encrypter->encryptId($data['subscriptionId'])
            )
        );
    }

    /**
     * @return array|null
     */
    public function getRelationshipReaders(): ?array
    {
        $response = [];

        /** @see UserIO::byUserId() */
        $response[] = new RelationshipBuilder(
            name: 'payer',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: UserIO::class,
                functionName: 'byUserId',
                parameters: ['payerId']
            )
        );

        /** @see UserIO::byUserId() */
        $response[] = new RelationshipBuilder(
            name: 'receiper',
            builderClassName: UserBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: UserIO::class,
                functionName: 'byUserId',
                parameters: ['receiperId']
            )
        );

        return $response;
    }

}