<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;

enum SubscriptionFrequency: string
{
    case Monthly = 'monthly';

    public function toStipeConstant(): string
    {
        return match ($this) {
            self::Monthly => 'month',
        };
    }
}