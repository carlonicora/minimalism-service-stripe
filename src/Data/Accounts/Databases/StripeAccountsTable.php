<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeAccounts', databaseIdentifier: 'Finance')]
enum StripeAccountsTable
{

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::PrimaryKey)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeAccountId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case email;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case status;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case payoutsEnabled;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}