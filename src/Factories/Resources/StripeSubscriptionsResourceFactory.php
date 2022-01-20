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
     * @param int $subscriptionId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byId(
        int $subscriptionId
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

    /**
     * @param string $stripeSubscriptionId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byStripeSubscriptionId(
        string $stripeSubscriptionId
    ): ResourceObject
    {
        /** @see StripeSubscriptionsTable::byStripeSubscriptionId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripeSubscriptionBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className: StripeSubscriptionsTable::class,
                functionName: 'byStripeSubscriptionId',
                parameters: ['stripeSubscriptionId' => $stripeSubscriptionId]
            )
        ));

        if ($result === false) {
            throw new RecordNotFoundException(message: 'Stripe subscription not found', code: 404);
        }

        return $result;
    }

    /**
     * @param string $stripePaymentIntentId
     * @return ResourceObject
     * @throws RecordNotFoundException
     */
    public function byStripeLastPaymentIntentId(
        string $stripePaymentIntentId
    ): ResourceObject
    {
        /** @see StripeSubscriptionsTable::byStripeLastPaymentIntentId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripeSubscriptionBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_TABLE,
                className: StripeSubscriptionsTable::class,
                functionName: 'byStripeLastPaymentIntentId',
                parameters: ['stripePaymentIntentId' => $stripePaymentIntentId]
            )
        ));

        if ($result === false) {
            throw new RecordNotFoundException(message: 'Stripe subscription not found by Stripe payment intent id', code: 404);
        }

        return $result;
    }
}