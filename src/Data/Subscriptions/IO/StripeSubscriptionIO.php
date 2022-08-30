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
    public function byRecieperAndPayerIdsActive(
        int $recieperId,
        int $payerId
    ): StripeSubscription
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::active()), comparison: SqlComparison::In),
            responseType: StripeSubscription::class
        );
    }

    /**
     * @param int $payerId
     * @param int $offset
     * @param int $limit
     * @return StripeSubscription[]
     * @throws MinimalismException
     */
    public function byPayerId(
        int $payerId,
        int $offset,
        int $limit
    ): array
    {
        $activeSubscriptions = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::active()), comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class,
            requireObjectsList: true
        );

        $inactiveSubscriptions = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::inactive()), comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class,
            requireObjectsList: true
        );

        return array_merge($activeSubscriptions, $inactiveSubscriptions);
    }

    /**
     * @param int $recieperId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MinimalismException
     */
    public function byRecieperId(
        int $recieperId,
        int $offset,
        int $limit
    ): array
    {
        $activeSubscriptions = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::active()), comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class,
            requireObjectsList: true
        );

        $inactiveSubscriptions = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::inactive()), comparison: SqlComparison::In)
                ->addOrderByFields([new SqlOrderByObject(field: StripeSubscriptionsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripeSubscription::class,
            requireObjectsList: true
        );

        return array_merge_recursive($activeSubscriptions, $inactiveSubscriptions);
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
    public function recieperIdsByPayerIdActive(
        int $payerId
    ): array
    {
        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)
                ->addParameter(field: StripeSubscriptionsTable::payerId, value: $payerId)
                ->addParameter(field: StripeSubscriptionsTable::status, value: self::prepareStatuses(SubscriptionStatus::active()), comparison: SqlComparison::In),
        );

        $recieperId = SqlQueryFactory::create(tableClass: StripeSubscriptionsTable::class)->getTable()
            ->getField(field: StripeSubscriptionsTable::recieperId)->getName();

        return array_column(array: $result, column_key: $recieperId);
    }

    /**
     * @param array $statuses
     * @return array
     */
    private static function prepareStatuses(
        array $statuses
    ): array
    {
        $result = [];
        foreach ($statuses as $status) {
            $result[] = "'" . $status->value . "'";
        }

        return $result;
    }

}