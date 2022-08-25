<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Data\SqlOrderByObject;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases\StripePaymentIntentsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripePaymentIntent;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Enums\PaymentIntentStatus;
use Exception;

class StripePaymentIntentIO extends AbstractSqlIO
{

    /**
     * @param int $id
     * @return StripePaymentIntent
     * @throws MinimalismException
     */
    public function byId(
        int $id
    ): StripePaymentIntent
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripePaymentIntentsTable::class)
                ->addParameter(field: StripePaymentIntentsTable::paymentIntentId, value: $id),
            responseType: StripePaymentIntent::class
        );
    }

    /**
     * @param int $recieperId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MinimalismException
     */
    public function byRecieperIdSucceeded(
        int $recieperId,
        int $offset,
        int $limit
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripePaymentIntentsTable::class)
                ->addParameter(field: StripePaymentIntentsTable::recieperId, value: $recieperId)
                ->addParameter(field: StripePaymentIntentsTable::status, value: PaymentIntentStatus::Succeeded)
                ->addOrderByFields([new SqlOrderByObject(field: StripePaymentIntentsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripePaymentIntent::class
        );
    }

    /**
     * @param int $payerId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws MinimalismException
     */
    public function byPayerIdSucceeded(
        int $payerId,
        int $offset,
        int $limit
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripePaymentIntentsTable::class)
                ->addParameter(field: StripePaymentIntentsTable::payerId, value: $payerId)
                ->addParameter(field: StripePaymentIntentsTable::status, value: PaymentIntentStatus::Succeeded)
                ->addOrderByFields([new SqlOrderByObject(field: StripePaymentIntentsTable::createdAt, isDesc: true)])
                ->limit(start: $offset, length: $limit),
            responseType: StripePaymentIntent::class
        );
    }

    /**
     * @param string $stripePaymentIntentId
     * @return StripePaymentIntent
     * @throws Exception
     */
    public function byStripePaymentIntentId(
        string $stripePaymentIntentId
    ): StripePaymentIntent
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripePaymentIntentsTable::class)
                ->addParameter(field: StripePaymentIntentsTable::stripePaymentIntentId, value: $stripePaymentIntentId),
            responseType: StripePaymentIntent::class
        );
    }

}