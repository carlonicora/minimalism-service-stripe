<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeEventsTable;

class StripeEventsDataReader extends AbstractLoader
{

    /**
     * @param string $id
     * @return array
     */
    public function byId(
        string $id
    ): array
    {
        /** @see StripeEventsTable::byId() */
        return $this->data->read(
            tableInterfaceClassName: StripeEventsTable::class,
            functionName: 'byId',
            parameters: ['id' => $id]
        );
    }
}