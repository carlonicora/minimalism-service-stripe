<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases\StripePaymentIntentsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripePaymentIntent;
use Exception;

class StripePaymentIntentIO extends AbstractSqlIO
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
            queryFactory: SqlQueryFactory::create(tableClass: StripePaymentIntentsTable::class)
                ->selectAll()
                ->addParameter(field: StripePaymentIntentsTable::paymentIntentId, value: $id)
        );

        return $this->returnSingleValue($result, recordType: 'Stripe payment intent');
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
                ->selectAll()
                ->addParameter(field: StripePaymentIntentsTable::stripePaymentIntentId, value: $stripePaymentIntentId),
            responseType: StripePaymentIntent::class
        );
    }

}