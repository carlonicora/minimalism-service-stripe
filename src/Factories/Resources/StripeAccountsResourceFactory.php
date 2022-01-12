<?php

namespace CarloNicora\Minimalism\Services\Stripe\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeAccountIO;

class StripeAccountsResourceFactory extends AbstractLoader
{

    /**
     * @param int $userId
     * @return ResourceObject
     */
    public function byUserId(
        int $userId
    ): ResourceObject
    {
        /** @see StripeAccountIO::byUserId() */
        return current($this->builder->build(
            resourceTransformerClass: StripeAccountBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: StripeAccountIO::class,
                functionName: 'byUserId',
                parameters: ['userId' => $userId],
            )
        ));
    }

}