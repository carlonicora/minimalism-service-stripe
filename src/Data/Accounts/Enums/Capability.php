<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Enums;

enum Capability: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}