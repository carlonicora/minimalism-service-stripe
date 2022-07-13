<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeEvents', databaseIdentifier: 'Finance')]
enum StripeEventsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::PrimaryKey)]
    case eventId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case type;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case relatedObjectId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case details;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

}