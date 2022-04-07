<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\ResourceBuilder\ResourceBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccount;
use Exception;

class StripeAccountsResourceFactory
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
     * @param StripeAccount $dataObject
     * @return ResourceObject
     * @throws Exception
     */
    public function byData(
        StripeAccount $dataObject
    ): ResourceObject
    {
        return $this->builder->buildResource(
            builderClass: StripeAccountBuilder::class,
            data: $dataObject,
        );
    }

}