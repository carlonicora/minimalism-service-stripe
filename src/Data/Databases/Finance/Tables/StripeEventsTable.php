<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;

class StripeEventsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeEvents';

    /** @var array */
    protected static array $fields = [
        'eventId' => FieldInterface::STRING +
            FieldInterface::PRIMARY_KEY,
        'type' => FieldInterface::STRING,
        'relatedObjectId' => FieldInterface::STRING,
        'details' => FieldInterface::STRING,
        'created' => FieldInterface::STRING
    ];

}