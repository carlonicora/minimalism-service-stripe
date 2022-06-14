<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Invoices\Databases;


use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeInvoices', databaseIdentifier: 'Finance')]
enum StripeInvoicesTable
{

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::AutoIncrement)]
    case invoiceId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeInvoiceId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeCustomerId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case subscriptionId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case payerId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case payerEmail;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case recieperId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case recieperEmail;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case frequency;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case amount;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case phlowFeePercent;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case currency;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case status;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}