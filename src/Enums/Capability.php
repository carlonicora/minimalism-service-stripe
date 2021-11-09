<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;

enum Capability: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}