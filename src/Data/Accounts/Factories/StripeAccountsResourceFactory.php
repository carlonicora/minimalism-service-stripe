<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccount;
use CarloNicora\Minimalism\Services\Users\Data\Abstracts\AbstractUserResourceFactory;
use Exception;

class StripeAccountsResourceFactory extends AbstractUserResourceFactory
{

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
            // TODO cacheBuilder
        );
    }

}