<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Invoices\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripeInvoices', databaseIdentifier: 'Finance')]
enum StripeInvoicesTable
{

    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case invoiceId;

    #[SqlField]
    case stripeInvoiceId;

    #[SqlField]
    case stripeCustomerId;

    #[SqlField(fieldType: FieldType::Integer)]
    case subscriptionId;

    #[SqlField(fieldType: FieldType::Integer)]
    case payerId;

    #[SqlField]
    case payerEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case recieperId;

    #[SqlField]
    case recieperEmail;

    #[SqlField]
    case frequency;

    #[SqlField(fieldType: FieldType::Integer)]
    case amount;

    #[SqlField(fieldType: FieldType::Integer)]
    case phlowFeePercent;

    #[SqlField]
    case currency;

    #[SqlField]
    case status;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}