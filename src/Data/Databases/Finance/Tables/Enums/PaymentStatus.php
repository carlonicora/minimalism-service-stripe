<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums;

enum PaymentStatus: int
{
    case Pending = 0;
    case Successful = 1;
    case Error = 2;
}