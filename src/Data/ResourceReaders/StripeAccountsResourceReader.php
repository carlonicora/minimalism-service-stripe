<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\StripeAccountBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeAccountsDataReader;

class StripeAccountsResourceReader extends AbstractLoader
{

    /**
     * @param int $userId
     * @return ResourceObject
     */
    public function byUserId(
        int $userId
    ): ResourceObject
    {
        /** @see StripeAccountsDataReader::byUserId() */
        return current($this->builder->build(
            resourceTransformerClass: StripeAccountBuilder::class,
            function:  new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: StripeAccountsDataReader::class,
                functionName: 'byUserId',
                parameters: ['userId' => $userId],
            )
        ));
    }

}