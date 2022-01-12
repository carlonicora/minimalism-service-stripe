<?php

namespace CarloNicora\Minimalism\Services\Stripe\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Builders\StripeSubscriptionBuilder;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeSubscriptionsTable;

class StripeSubscriptionsResourceFactory extends AbstractLoader
{

    /**
     * @param string $subscriptionId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byId(
        string $subscriptionId
    ): ResourceObject
    {
        /** @see StripeSubscriptionsTable::readById() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripeSubscriptionBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className: StripeSubscriptionsTable::class,
                functionName: 'readById',
                parameters: ['id' => $subscriptionId]
            )
        ));

        if ($result === false) {
            throw new RecordNotFoundException(message: 'Stripe subscription not found', code: 404);
        }

        return $result;
    }
}