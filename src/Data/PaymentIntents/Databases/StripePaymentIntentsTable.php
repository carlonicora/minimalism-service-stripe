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

    #[SqlField]
    case stripePaymentIntentId;

    #[SqlField]
    case stripeInvoiceId;

    #[SqlField(fieldType: FieldType::Integer)]
    case payerId;

    #[SqlField]
    case payerEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case recieperId;

    #[SqlField]
    case recieperAccountId;

    #[SqlField]
    case recieperEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case amount;

    #[SqlField]
    case currency;

    #[SqlField(fieldType: FieldType::Integer)]
    case phlowFeeAmount;

    #[SqlField]
    case status;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}