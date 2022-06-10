<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripePaymentIntents', databaseIdentifier: 'Finance')]
enum StripePaymentIntentsTable
{

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::AutoIncrement)]
    case paymentIntentId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripePaymentIntentId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeInvoiceId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case payerId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case payerEmail;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case recieperId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case recieperAccountId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case recieperEmail;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case amount;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case currency;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case phlowFeeAmount;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case status;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}