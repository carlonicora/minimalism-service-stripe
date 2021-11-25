<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeEventsTable;

class StripeEventsDataWriter extends AbstractLoader
{

    /**
     * @param string $eventId
     * @param string $type
     * @param string $createdAt
     * @param string $relatedObjectId
     * @param string|null $details
     * @return array
     */
    public function create(
        string $eventId,
        string $type,
        string $createdAt,
        string $relatedObjectId,
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