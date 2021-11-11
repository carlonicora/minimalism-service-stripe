<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\StripePaymentIntentBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentIntentsTable;

class StripePaymentIntentsResourceReader extends AbstractLoader
{

    /**
     * @param string $paymentIntentId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byId(
        string $paymentIntentId
    ): ResourceObject
    {
        /** @see StripePaymentIntentsTable::byId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripePaymentIntentBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className:StripePaymentIntentsTable::class,
                functionName: 'byId',
                parameters: [$paymentIntentId]
            )
        ));

        if ($result === false) {
            throw new RecordNotFoundException(message: 'Stripe payment intent not found', code: 404);
        }

        return $result;
    }
}