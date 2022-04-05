<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\Databases\StripeEventsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\DataObjects\StripeEvent;

class StripeEventIO  extends AbstractSqlIO
{

    /**
     * @param string $id
     * @return array
     * @throws MinimalismException
     */
    public function byId(
        string $id
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeEventsTable::class)
                ->selectAll()
                ->addParameter(field: StripeEventsTable::eventId, value: $id)
        );
    }

    /**
     * @param StripeEvent $dataObject
     * @return array
     */
    public function create(
        StripeEvent $dataObject
    ): array
    {
        return $this->data->create(
            queryFactory: $dataObject
        );
    }
}