<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripeProducts', databaseIdentifier: 'Finance')]
enum StripeProductsTable
{
    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case productId;

    #[SqlField]
    case stripeProductId;

    #[SqlField(fieldType: FieldType::Integer)]
    case recieperId;

    #[SqlField]
    case name;

    #[SqlField]
    case description;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}