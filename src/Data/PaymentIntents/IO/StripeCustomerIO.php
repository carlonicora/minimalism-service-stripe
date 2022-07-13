<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases\StripeCustomersTable;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects\StripeCustomer;

class StripeCustomerIO extends AbstractSqlIO
{

    /**
     * @param int $userId
     * @return StripeCustomer
     * @throws MinimalismException
     */
    public function byUserId(
        int $userId
    ): StripeCustomer
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeCustomersTable::class)
                ->addParameter(field: StripeCustomersTable::userId, value: $userId),
            responseType: StripeCustomer::class
        );
    }

    /**
     * @param int $stripeCustomerId
     * @return array
     * @throws MinimalismException
     */
    public function byStripeCustomerId(
        int $stripeCustomerId
    ): array
    {

        $result = $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeCustomersTable::class)
                ->addParameter(field: StripeCustomersTable::stripeCustomerId, value: $stripeCustomerId)
        );

        return $this->returnSingleValue($result, recordType: 'Stripe customer');
    }

}