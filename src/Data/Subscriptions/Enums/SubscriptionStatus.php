<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Enums;

enum SubscriptionStatus: string
{
    // If initial payment fails
    case Incomplete = 'incomplete';
    // Initial payment failed and not paid within 23 hours. We should create a new subscription.
    case IncompleteExpired = 'incomplete_expired';
    // If the trial period is configured
    case Trialing = 'trialing';
    // Initial payment succeeded
    case Active = 'active';
    // Stripe has failed to make n-th payment (n > 1)
    case PastDue = 'past_due';
    // The user has canceled the subscription
    case Canceled = 'canceled';
    // Stripe has exhausted all payments attempts
    case Unpaid = 'unpaid';
}