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

    #[SqlField]
    case stripeSubscriptionId;

    #[SqlField]
    case stripeLastInvoiceId;

    #[SqlField]
    case stripeLastPaymentIntentId;

    #[SqlField]
    case stripePriceId;

    #[SqlField(fieldType: FieldType::Integer)]
    case productId;

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

    #[SqlField]
    case currentPeriodEnd;

    #[SqlField(fieldOption: FieldOption::TimeCreate)]
    case createdAt;

    #[SqlField(fieldOption: FieldOption::TimeUpdate)]
    case updatedAt;

}