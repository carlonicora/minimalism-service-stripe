<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeProducts', databaseIdentifier: 'Finance')]
enum StripeProductsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::AutoIncrement)]
    case productId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeProductId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case recieperId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case name;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case description;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}