<?php

namespace CarloNicora\Minimalism\Services\Stripe\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Builders\StripePaymentIntentBuilder;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripePaymentIntentsTable;

class StripePaymentIntentsResourceFactory extends AbstractLoader
{

    /**
     * @param string $paymentIntentId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byStripePaymentIntentId(
        string $paymentIntentId
    ): ResourceObject
    {
        /** @see StripePaymentIntentsTable::byStripePaymentIntentId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripePaymentIntentBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className: StripePaymentIntentsTable::class,
                functionName: 'byStripePaymentIntentId',
                parameters: ['paymentIntentId' => $paymentIntentId]
            )
        ));

        if ($result === false) {
            throw new RecordNotFoundException(message: 'Stripe payment intent not found', code: 404);
        }

        return $result;
    }
}