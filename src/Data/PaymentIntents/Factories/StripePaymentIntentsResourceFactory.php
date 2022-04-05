<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Builders\StripePaymentIntentBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripePaymentIntent;
use CarloNicora\Minimalism\Services\Users\Data\Abstracts\AbstractUserResourceFactory;
use Exception;

class StripePaymentIntentsResourceFactory extends AbstractUserResourceFactory
{

    /**
     * @param StripePaymentIntent $dataObject
     * @return ResourceObject
     * @throws Exception
     */
    public function byData(
        StripePaymentIntent $dataObject
    ): ResourceObject
    {
        return $this->builder->buildResource(
            builderClass: StripePaymentIntentBuilder::class,
            data: $dataObject,
        );
    }

}