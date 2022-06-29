<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\ResourceBuilder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use CarloNicora\Minimalism\Services\Stripe\Dictionary\StripeDictionary;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Users\Data\Dictionary\UsersDictionary;
use Exception;
use OpenApi\Annotations as OA;

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
 *     @OA\Property(property="recieperStripeAccountId", type="string", format="", nullable=false, minLength="1", maxLength="100", example="acct_1JikcyJVYb6RvKNf"),
 *     @OA\Property(property="recieper",
 *         @OA\Property(property="amount", type="number", format="int32", nullable=false, minimum="0", maximum="1000", example="123"),
 *         @OA\Property(property="cents", type="number", format="int32", nullable=true, minimum="0", maximum="99", example="99"),
 *         @OA\Property(property="currency", type="string", format="", nullable=false, minLength="3", maxLength="3", example="gbp")
 *     ),
 *     @OA\Property(property="phlowFeePercent", type="number", format="int32", nullable=false, minimum="0", maximum="100", example="15"),
 *     @OA\Property(property="status", type="number", format="int32", nullable=false, minimum="0", maximum="3", example="1"),
 *     @OA\Property(property="currentPeriodEnd", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=false, example="2021-01-01 23:59:59"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true, example="2021-01-01 23:59:59")
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscriptionLinks",
 *     title="Stripe payment intent links",
 *     description="Stripe payment intent resource links",
 *     @OA\Property(property="self", type="string", format="uri", nullable=false, minLength="1", maxLength="100", example="https://api.phlow.com/v2.5/stripe/subscriptions/pi_3JwjWIJVYb6RvKNf0QzDSKYp")
 * )
 *
 * @OA\Schema(
 *     schema="stripeSubscriptionRelationships",
 *     title="Stripe payment intent relationships",
 *     description="Stripe payment intent resource relationships",
 *     @OA\Property(property="recieper", ref="#/components/schemas/user"),
 *     @OA\Property(property="payer", ref="#/components/schemas/user")
 * )
 */
class StripeSubscriptionBuilder extends AbstractResourceBuilder
{

    /**
     * @param EncrypterInterface $encrypter
     */
    public function __construct(
        protected readonly EncrypterInterface $encrypter,
    )
    {
    }

    /**
     * @param StripeSubscription|ResourceableDataInterface $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        StripeSubscription|ResourceableDataInterface $data
    ): ResourceObject
    {
        [$amountInt, $amountCents] = Amount::fromCents($data->getAmount(), $data->getCurrency());

        $encryptedId = $this->encrypter->encryptId($data->getId());

        $response = new ResourceObject(
            type: StripeDictionary::StripeSubscriptions->value,
            id: $encryptedId,
        );

        $response->attributes->add(name: 'stripeSubscriptionId', value: $data->getStripeSubscriptionId());
        $response->attributes->add(name: 'stripePriceId', value: $data->getStripePriceId());
        // client secret should be set later in the code
        $response->attributes->add(name: 'clientSecret', value: null);
        // recieper stripe account id should be set later in the code
        $response->attributes->add(name: 'recieperStripeAccountId', value: null);
        $response->attributes->add(name: 'frequency', value: $data->getFrequency());
        $response->attributes->add(name: 'recieper', value: ['amount' => $amountInt, 'cents' => $amountCents, 'currency' => $data->getCurrency()]);
        $response->attributes->add(name: 'phlowFeePercent', value: $data->getPhlowFeePercent());
        $response->attributes->add(name: 'status', value: $data->getStatus());
        $response->attributes->add(name: 'currentPeriodEnd', value: $data->getCurrentPeriodEnd());
        $response->attributes->add(name: 'createdAt', value: $data->getCreatedAt());
        $response->attributes->add(name: 'updatedAt', value: $data->getUpdatedAt());

        $response->links->add(new Link(
            name: 'self',
            href: 'stripe/' . StripeDictionary::StripeSubscriptions->getEndpoint() . '/' . $encryptedId
        ));

        $response->relationship(relationshipKey: 'payer')
            ->links->add(new Link(
                name:'related',
                href: UsersDictionary::User->getEndpoint()
                    . $this->encrypter->encryptId($data->getPayerId())
            ));

        $response->relationship(relationshipKey: 'recieper')
            ->links->add(new Link(
                name:'related',
                href: UsersDictionary::User->getEndpoint()
                    . $this->encrypter->encryptId($data->getRecieperId())
            ));

        return $response;
    }

}