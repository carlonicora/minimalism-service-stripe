<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Data\SqlOrderByObject;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlComparison;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlFieldFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlTableFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeSubscriptionsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeSubscription;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Enums\SubscriptionStatus;


class StripeSubscriptionIO extends AbstractSqlIO
{

    /**
     * @param int $id
     * @return StripeSubscription
     * @throws MinimalismException
     */
    public function byId(
        int $id
    ): StripeSubscription
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::subscriptionId, value: $id),
            responseType: StripeSubscription::class
        );
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
     * @param int $payerId
     * @param bool $inactive
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MinimalismException
     */
    public function byPayerId(
        int  $payerId,
        bool $inactive,
        int  $offset,
        int  $limit
    ): array
    {
        if ($inactive) {
            $statuses = [SubscriptionStatus::Canceled, SubscriptionStatus::Unpaid];
        } else {
            $statuses = [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue];
        }

        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: $statuses, comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class
        );
    }

    /**
     * @param int $recieperId
     * @param bool $inactive
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MinimalismException
     */
    public function byRecieperId(
        int  $recieperId,
        bool $inactive,
        int  $offset,
        int  $limit
    ): array
    {
        if ($inactive) {
            $statuses = [SubscriptionStatus::Canceled, SubscriptionStatus::Unpaid];
        } else {
            $statuses = [SubscriptionStatus::Trialing, SubscriptionStatus::Active, SubscriptionStatus::PastDue];
        }

        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: $statuses, comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class
        );
    }

    /**
     * @param int $userId
     * @return StripeSubscription[]
     * @throws MinimalismException
     */
    public function byRecieperOrPayerId(
        int $userId,
    ): array
    {
        $subscriptions = SqlTableFactory::create(tableClass: StripeSubscriptionsTable::class)->getFullName();

        $recieperId = SqlFieldFactory::create(field: StripeSubscriptionsTable::recieperId)->getFullName();
        $payerId    = SqlFieldFactory::create(field: StripeSubscriptionsTable::payerId)->getFullName();

        $sql = ' SELECT * '
            . ' FROM ' . $subscriptions
            . ' WHERE ' . $recieperId . '=? OR ' . $payerId . '=?';

        $queryFactory = SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class);
        $queryFactory->addParameter(field: StripeSubscriptionsTable::recieperId, value: $userId)
            ->addParameter(field: StripeSubscriptionsTable::payerId, value: $userId);
        $queryFactory->setSql($sql);

        return $this->data->read(
            queryFactory: $queryFactory,
            responseType: StripeSubscription::class,
            requireObjectsList: true
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