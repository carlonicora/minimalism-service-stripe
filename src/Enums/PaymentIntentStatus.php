<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;

enum PaymentIntentStatus: string
{
    // Either first step of a payment, either payment failed
    case RequiresPaymentMethod = 'requires_payment_method';
    // Some payment methods requires to confirm a payment after filing all the data
    case RequiresConfirmation = 'requires_confirmation';
    // i. e. 3d secure
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    // Status is possible only if we charge a user now, but capture funds later
    case RequiresCapture = 'requires_capture';
    case Canceled = 'canceled';
    case Succeeded = 'succeeded';
}