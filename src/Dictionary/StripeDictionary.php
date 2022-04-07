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
            self::StripeAccounts       => 'accounts',
            self::SrtipeAccountsLinks  => 'accounts/links',
            self::StripePaymentIntents => 'paymentIntents',
            self::StripeSubscriptions  => 'subscriptions',
        };
    }
}