<?php

namespace CarloNicora\Minimalism\Services\Stripe\Dictionary;

enum StripeDictionary: string
{
    case StripeAccounts = 'stripeAccount';
    case SrtipeAccountsLinks = 'stripeAccountLink';
    case StripePaymentIntents = 'stripePaymentIntent';
    case StripeSubscriptions = 'stripeSubscription';
    case StripeSubscriptionSidecar = 'stripeSubscriptionSidecar';
    case StripePayerSubscriptions = 'stripePayerSubscriptions';
    case StripeRecieperSubscriptions = 'stripeRecieperSubscriptions';
    case StripePayerTips = 'stripePayerTips';
    case StripeRecieperTips = 'stripeRecieperTips';

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return match ($this) {
            self::StripeAccounts              => 'stripe/accounts',
            self::SrtipeAccountsLinks         => 'stripe/accounts/links',
            self::StripePaymentIntents        => 'stripe/paymentIntents',
            self::StripeSubscriptions         => 'stripe/subscriptions',
            self::StripeSubscriptionSidecar   => 'stripe/subscriptions/sidecars',
            self::StripePayerSubscriptions    => 'stripe/payers/subscriptions',
            self::StripeRecieperSubscriptions => 'stripe/reciepers/subscriptions',
            self::StripePayerTips             => 'stripe/payers/tips',
            self::StripeRecieperTips          => 'stripe/reciepers/tips',
        };
    }

}