<?php

namespace CarloNicora\Minimalism\Services\Stripe\Factories\Resources;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\Data\Interfaces\DataFunctionInterface;
use CarloNicora\Minimalism\Interfaces\Data\Objects\DataFunction;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Builders\StripeSubscriptionBuilder;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeSubscriptionIO;

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
        /** @see StripeSubscriptionIO::byId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripeSubscriptionBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: StripeSubscriptionIO::class,
                functionName: 'byId',
                parameters: ['subscriptionId' => $subscriptionId]
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
        /** @see StripeSubscriptionIO::byStripeSubscriptionId() */
        $result = current($this->builder->build(
            resourceTransformerClass: StripeSubscriptionBuilder::class,
            function: new DataFunction(
                type: DataFunctionInterface::TYPE_LOADER,
                className: StripeSubscriptionIO::class,
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
     * @param int $recieperId
     * @param int $payerId
     * @return ResourceObject
     */
    public function byRecieperAndPayerIds(
        int $recieperId,
        int $payerId
    ): ResourceObject
    {
        /** @see StripeSubscriptionIO::byRecieperAndPayerIds() */
        return current(
            $this->builder->build(
                resourceTransformerClass: StripeSubscriptionBuilder::class,
                function: new DataFunction(
                    type: DataFunctionInterface::TYPE_LOADER,
                    className: StripeSubscriptionIO::class,
                    functionName: 'byRecieperAndPayerIds',
                    parameters: [
                        'recieperId' => $recieperId,
                        'payerId' => $payerId
                    ]
                )
            )
        );
    }
}