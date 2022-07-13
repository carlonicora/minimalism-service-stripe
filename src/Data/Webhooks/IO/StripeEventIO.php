<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\Databases\StripeEventsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\DataObjects\StripeEvent;

class StripeEventIO  extends AbstractSqlIO
{

    /**
     * @param string $id
     * @return StripeEvent
     * @throws MinimalismException
     */
    public function byId(
        string $id
    ): StripeEvent
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeEventsTable::class)
                ->addParameter(field: StripeEventsTable::eventId, value: $id),
            responseType: StripeEvent::class
        );
    }

}