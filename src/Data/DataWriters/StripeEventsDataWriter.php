<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeEventsTable;
use Stripe\Event;

class StripeEventsDataWriter extends AbstractLoader
{

    /**
     * @param Event $event
     * @return array
     */
    public function create(
        Event $event
    ): array
    {
        $records = [
            'eventId' => $event->id,
            'type' => $event->type,
            'dataObjectId' => $event->data->object->id ?? null,
            'created' => date(format: 'Y-m-d H:i:s', timestamp: $event->created)
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeEventsTable::class,
            records: $records
        );
    }
}