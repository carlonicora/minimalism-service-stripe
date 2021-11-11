<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeEventsTable;

class StripeEventsDataWriter extends AbstractLoader
{

    /**
     * @param string $eventId
     * @param string $type
     * @param int $created
     * @param string $relatedObjectId
     * @param array|null $details
     * @return array
     */
    public function create(
        string $eventId,
        string $type,
        int    $created,
        string $relatedObjectId,
        array  $details = null
    ): array
    {
        $records = [
            'eventId' => $eventId,
            'type' => $type,
            'objectId' => $relatedObjectId,
            'details' => $details,
            'createdAt' => $created
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeEventsTable::class,
            records: $records
        );
    }
}