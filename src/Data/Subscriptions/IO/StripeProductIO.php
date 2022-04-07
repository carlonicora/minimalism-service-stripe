<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeProductsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects\StripeProduct;

class StripeProductIO extends AbstractSqlIO
{

    /**
     * @param int $recieperId
     * @return StripeProduct
     * @throws MinimalismException
     */
    public function byRecieperId(
        int $recieperId,
    ): StripeProduct
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeProductsTable::class)
                ->selectAll()
                ->addParameter(field: StripeProductsTable::recieperId, value: $recieperId),
            responseType: StripeProduct::class
        );
    }

}