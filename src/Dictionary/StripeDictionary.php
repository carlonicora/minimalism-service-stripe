<?php

namespace CarloNicora\Minimalism\Services\Stripe\Dictionary;

enum StripeDictionary: string
{
    case StripeAccounts = 'stripeAccount';
    case SrtipeAccountsLinks = 'stripeAccountLink';
    case StripePaymentIntents = 'stripePaymentIntent';
    case StripeSubscriptions = 'stripeSubscription';

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return match ($this) {
            self::StripeAccounts       => 'stripe/accounts',
            self::SrtipeAccountsLinks  => 'stripe/accounts/links',
            self::StripePaymentIntents => 'stripe/paymentIntents',
            self::StripeSubscriptions  => 'stripe/subscriptions',
        };
    }
}