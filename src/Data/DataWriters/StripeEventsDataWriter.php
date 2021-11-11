<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeEventsTable;
use JsonException;

class StripeEventsDataWriter extends AbstractLoader
{

    /**
     * @param string $eventId
     * @param string $type
     * @param int $created
     * @param string $relatedObjectId
     * @param array|null $details
     * @return array
     * @throws JsonException
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
            'details' => $details ? json_encode($details, flags: JSON_THROW_ON_ERROR) : null,
            'createdAt' => date(format: 'Y-m-d H:i:s', timestamp: $created)
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeEventsTable::class,
            records: $records
        );
    }
}