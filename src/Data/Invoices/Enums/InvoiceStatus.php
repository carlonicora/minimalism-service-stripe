<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Invoices\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case Void = 'void';
    case Uncollectible = 'uncollectible';
}