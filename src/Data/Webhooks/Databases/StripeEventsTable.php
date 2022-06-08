<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripeEvents', databaseIdentifier: 'Finance')]
enum StripeEventsTable
{
    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case eventId;

    #[SqlField]
    case type;

    #[SqlField]
    case relatedObjectId;

    #[SqlField]
    case details;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

}