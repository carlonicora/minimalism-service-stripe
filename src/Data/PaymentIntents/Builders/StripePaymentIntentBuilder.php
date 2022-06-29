<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Builders;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Encrypter\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\ResourceBuilder\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripePaymentIntent;
use CarloNicora\Minimalism\Services\Stripe\Dictionary\StripeDictionary;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use CarloNicora\Minimalism\Services\Users\Data\Dictionary\UsersDictionary;
use Exception;
use OpenApi\Annotations as OA;

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
 *     @OA\Property(property="recieper", ref="#/components/schemas/user"),
 *     @OA\Property(property="payer", ref="#/components/schemas/user")
 * )
 */
class StripePaymentIntentBuilder extends AbstractResourceBuilder
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
     * @param StripePaymentIntent|ResourceableDataInterface $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResource(
        StripePaymentIntent|ResourceableDataInterface $data
    ): ResourceObject
    {
        [$amountInt, $amountCents] = Amount::fromCents($data->getAmount(), $data->getCurrency());
        [$feeAmountInt, $feeAmountCents] = Amount::fromCents($data->getPhlowFeeAmount(), $data->getCurrency());

        $encryptedId = $this->encrypter->encryptId($data->getId());

        $response = new ResourceObject(
            type: StripeDictionary::StripePaymentIntents->value,
            id: $encryptedId,
        );

        $response->attributes->add(name: 'stripePaymentIntentId', value: $data->getStripePaymentIntentId());
        $response->attributes->add(name: 'clientSecret', value: $data->getClientSecret());
        $response->attributes->add(
            name: 'amount',
            value: ['integer' => $amountInt, 'cents' => $amountCents, 'currency' => $data->getCurrency()]
        );
        $response->attributes->add(
            name: 'phlowFeeAmount',
            value: ['integer' => $feeAmountInt, 'cents' => $feeAmountCents, 'currency' => $data->getCurrency()]
        );
        $response->attributes->add(name: 'status', value: $data->getStatus());
        $response->attributes->add(name: 'createdAt', value: $data->getCreatedAt());
        $response->attributes->add(name: 'updatedAt', value: $data->getUpdatedAt());

        $response->links->add(new Link(
            name: 'self',
            href: 'stripe/' . StripeDictionary::StripePaymentIntents->getEndpoint() . '/' . $data->getStripePaymentIntentId()
        ));

        $response->relationship(relationshipKey: 'payer')
            ->links->add(new Link(
                name:'related',
                href: UsersDictionary::User->getEndpoint() . $this->encrypter->encryptId($data->getPayerId())
            ));

        $response->relationship(relationshipKey: 'recieper')
        ->links->add(new Link(
            name:'related',
            href: UsersDictionary::User->getEndpoint() . $this->encrypter->encryptId($data->getRecieperId())
        ));

        return $response;
    }

}