<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlFieldAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\SqlTableAttribute;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldOption;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\SqlFieldType;

#[SqlTableAttribute(name: 'stripeSubscriptions', databaseIdentifier: 'Finance')]
enum StripeSubscriptionsTable
{
    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer, fieldOption: SqlFieldOption::AutoIncrement)]
    case subscriptionId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeSubscriptionId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeLastInvoiceId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripeLastPaymentIntentId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case stripePriceId;

    #[SqlFieldAttribute(fieldType: SqlFieldType::Integer)]
    case productId;

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

    #[SqlFieldAttribute(fieldType: SqlFieldType::String)]
    case currentPeriodEnd;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeCreate)]
    case createdAt;

    #[SqlFieldAttribute(fieldType: SqlFieldType::String, fieldOption: SqlFieldOption::TimeUpdate)]
    case updatedAt;

}