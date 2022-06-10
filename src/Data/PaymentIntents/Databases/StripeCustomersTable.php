<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeCustomers', databaseIdentifier: 'Finance')]
enum StripeCustomersTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::PrimaryKey)]
    case userId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeCustomerId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case email;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}