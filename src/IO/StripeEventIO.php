<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeEventsTable;

class StripeEventIO extends AbstractLoader
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

    /**
     * @param string $eventId
     * @param string $type
     * @param string $createdAt
     * @param string|null $relatedObjectId
     * @param string|null $details
     * @return array
     */
    public function create(
        string $eventId,
        string $type,
        string $createdAt,
        string $relatedObjectId = null,
        string $details = null
    ): array
    {
        $records = [
            'eventId' => $eventId,
            'type' => $type,
            'relatedObjectId' => $relatedObjectId,
            'details' => $details,
            'createdAt' => $createdAt
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeEventsTable::class,
            records: $records
        );
    }
}