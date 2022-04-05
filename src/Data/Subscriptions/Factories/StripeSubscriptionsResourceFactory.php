<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use CarloNicora\Minimalism\Services\Users\Data\Abstracts\AbstractUserResourceFactory;
use Exception;

class StripeSubscriptionsResourceFactory extends AbstractUserResourceFactory
{
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