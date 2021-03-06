<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeSubscriptionsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;


class StripeSubscriptionIO extends AbstractSqlIO
{

    /**
     * @param int $id
     * @return array
     * @throws MinimalismException
     */
    public function byId(
        int $id
    ): array
    {
        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::subscriptionId, value: $id)
        );

        return $this->returnSingleValue($result, recordType: 'Stripe subscription');
    }

    /**
     * @param int $recieperId
     * @param int $payerId
     * @return StripeSubscription
     * @throws MinimalismException
     */
    public function byRecieperAndPayerIds(
        int $recieperId,
        int $payerId
    ): StripeSubscription
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId),
            responseType: StripeSubscription::class
        );
    }

    /**
     * @param string $stripeSubscriptionId
     * @return StripeSubscription
     * @throws MinimalismException
     */
    public function byStripeSubscriptionId(
        string $stripeSubscriptionId
    ): StripeSubscription
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::stripeSubscriptionId, value: $stripeSubscriptionId),
            responseType: StripeSubscription::class
        );
    }

    /**
     * @param int $payerId
     * @return int[]
     * @throws MinimalismException
     */
    public function recieperIdsByPayerId(
        int $payerId
    ): array
    {
        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
        );

        $recieperId = SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)->getTable()
            ->getField(field: StripeSubscriptionsTable::recieperId)->getName();

        return array_column(array: $result, column_key: $recieperId);
    }

}