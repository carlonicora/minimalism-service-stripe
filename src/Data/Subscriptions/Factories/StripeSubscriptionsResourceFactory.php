<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\SimpleObjectInterface;
use CarloNicora\Minimalism\Services\ResourceBuilder\ResourceBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Builders\StripeSubscriptionBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use Exception;

class StripeSubscriptionsResourceFactory implements SimpleObjectInterface
{
    /**
     * @param ResourceBuilder $builder
     */
    public function __construct(
        protected ResourceBuilder $builder,
    )
    {

    }

    /**
     * @param StripeSubscription $dataObject
     * @return ResourceObject
     * @throws Exception
     */
    public function byData(
        StripeSubscription $dataObject
    ): ResourceObject
    {
        return $this->builder->buildResource(
            builderClass: StripeSubscriptionBuilder::class,
            data: $dataObject,
        );
    }

}