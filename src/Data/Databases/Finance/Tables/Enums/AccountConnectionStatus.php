<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums;

enum AccountConnectionStatus: int
{
    case Pending = 0;
    case Success = 1;
    case Error = 2;
}