<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripeAccounts', databaseIdentifier: 'Finance')]
enum StripeAccountsTable
{

    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::PrimaryKey)]
    case userId;

    #[SqlField(fieldType: FieldType::String)]
    case stripeAccountId;

    #[SqlField(fieldType: FieldType::String)]
    case email;

    #[SqlField(fieldType: FieldType::String)]
    case status;

    #[SqlField(fieldType: FieldType::Integer)]
    case payoutsEnabled;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}