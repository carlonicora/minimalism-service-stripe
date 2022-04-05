<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripePaymentIntents', databaseIdentifier: 'Finance')]
enum StripePaymentIntentsTable
{

    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case paymentIntentId;

    #[SqlField(fieldType: FieldType::String)]
    case stripePaymentIntentId;

    #[SqlField(fieldType: FieldType::String)]
    case stripeInvoiceId;

    #[SqlField(fieldType: FieldType::Integer)]
    case payerId;

    #[SqlField(fieldType: FieldType::String)]
    case payerEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case recieperId;

    #[SqlField(fieldType: FieldType::String)]
    case recieperAccountId;

    #[SqlField(fieldType: FieldType::String)]
    case recieperEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case amount;

    #[SqlField(fieldType: FieldType::String)]
    case currency;

    #[SqlField(fieldType: FieldType::Integer)]
    case phlowFeeAmount;

    #[SqlField(fieldType: FieldType::String)]
    case status;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}