<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Objects\DataFunction;
use CarloNicora\Minimalism\Services\Stripe\Data\Builders\StripePaymentBuilder;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentsTable;

class StripePaymentsResourceReader extends AbstractLoader
{

    /**
     * @param int $paymentId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byId(
        int $paymentId
    ): ResourceObject
    {
        /** @see StripePaymentsTable::byId() */
        $result = $this->builder->build(
            resourceTransformerClass: StripePaymentBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className:StripePaymentsTable::class,
                functionName: 'byId',
                parameters: [$paymentId]
            )
        );

        if (empty($result)) {
            throw new RecordNotFoundException(message: 'Stripe payment not found', code: 404);
        }

        return current($result);
    }
}