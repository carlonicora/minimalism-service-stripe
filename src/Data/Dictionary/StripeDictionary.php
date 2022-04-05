<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Dictionary;

enum StripeDictionary: string
{
    case StripeAccounts = 'stripeAccount';
    case SrtipeAccountsLinks = 'stripeAccountLink';
    case StripePaymentIntents = 'stripePaymentIntent';
    case StripeSubscriptions = 'stripeSubscription';
    case UserPaymentIntents = 'TODO1';
    case UserSubscriptions = 'TODO2';
    case StripeWebhooksAccounts = 'TODO3';
    case StripeWebhooksInvoices = 'TODO4';
    case StripeWebhooksPayments = 'TODO5';
    case StripeWebhooksSubscriptions = 'TODO6';

    /**
     * @param string $id
     * @return string
     */
    public function getEndpoint(
        string $id
    ): string
    {
        // TODO do we need all these endpoints? As far as I remember, we use this method in builders in relationships links
        return match ($this) {
            self::StripeAccounts              => 'stripe/accounts',
            self::SrtipeAccountsLinks         => 'stripe/accounts/links',
            self::StripePaymentIntents        => 'stripe/' . $id . '/paymentIntents',
            self::StripeSubscriptions         => 'stripe/subscriptions/' . $id,
            self::UserPaymentIntents          => 'users/' . $id . '/paymentIntents',
            self::UserSubscriptions           => 'stripe/' . $id . '/subscriptions',
            self::StripeWebhooksAccounts      => 'stripe/webhooks/accounts',
            self::StripeWebhooksInvoices      => 'stripe/webhooks/invoices',
            self::StripeWebhooksPayments      => 'stripe/webhooks/payments',
            self::StripeWebhooksSubscriptions => 'stripe/webhooks/subscriptions',
        };
    }
}