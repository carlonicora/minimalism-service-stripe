<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases;

use CarloNicora\Minimalism\Services\MySQL\Data\SqlField;
use CarloNicora\Minimalism\Services\MySQL\Data\SqlTable;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldOption;
use CarloNicora\Minimalism\Services\MySQL\Enums\FieldType;

#[SqlTable(name: 'stripeSubscriptions', databaseIdentifier: 'Finance')]
enum StripeSubscriptionsTable
{
    #[SqlField(fieldType: FieldType::Integer, fieldOption: FieldOption::AutoIncrement)]
    case subscriptionId;

    #[SqlField(fieldType: FieldType::String)]
    case stripeSubscriptionId;

    #[SqlField(fieldType: FieldType::String)]
    case stripeLastInvoiceId;

    #[SqlField(fieldType: FieldType::String)]
    case stripeLastPaymentIntentId;

    #[SqlField(fieldType: FieldType::String)]
    case stripePriceId;

    #[SqlField(fieldType: FieldType::Integer)]
    case productId;

    #[SqlField(fieldType: FieldType::Integer)]
    case payerId;

    #[SqlField(fieldType: FieldType::String)]
    case payerEmail;

    #[SqlField(fieldType: FieldType::Integer)]
    case recieperId;

    #[SqlField(fieldType: FieldType::String)]
    case recieperEmail;

    #[SqlField(fieldType: FieldType::String)]
    case frequency;

    #[SqlField(fieldType: FieldType::Integer)]
    case amount;

    #[SqlField(fieldType: FieldType::Integer)]
    case phlowFeePercent;

    #[SqlField(fieldType: FieldType::String)]
    case currency;

    #[SqlField(fieldType: FieldType::String)]
    case status;

    #[SqlField(fieldType: FieldType::String)]
    case currentPeriodEnd;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}