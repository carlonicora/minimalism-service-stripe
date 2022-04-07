<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\ResourceBuilder\ResourceBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use Exception;

class StripeSubscriptionsResourceFactory
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
            builderClass: StripeAccountBuilder::class,
            data: $dataObject,
        );
    }

}