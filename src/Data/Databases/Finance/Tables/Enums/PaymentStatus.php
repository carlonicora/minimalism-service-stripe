<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums;

enum PaymentStatus: int
{
    case Created = 0;
    case Sent = 1;
    case Successful = 2;
    case Error = 3;
}