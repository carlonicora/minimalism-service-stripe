<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeInvoicesTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\InvoiceStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;

class StripeInvoiceIO extends AbstractLoader
{

    /**
     * @param string $stripeInvoiceId
     * @param string $stripeCustomerId
     * @param int $payerId
     * @param string $payerEmail
     * @param int $receiperId
     * @param string $receiperEmail
     * @param int $amount
     * @param int $phlowFeePercent
     * @param Currency $currency
     * @param InvoiceStatus $invoiceStatus
     * @param SubscriptionFrequency $frequency
     * @param int|null $subscriptionId
     * @return array
     */
    public function create(
        string                $stripeInvoiceId,
        string                $stripeCustomerId,
        int                   $payerId,
        string                $payerEmail,
        int                   $receiperId,
        string                $receiperEmail,
        int                   $amount,
        int                   $phlowFeePercent,
        Currency              $currency,
        InvoiceStatus         $invoiceStatus,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly,
        int                   $subscriptionId = null
    ): array
    {
        $invoice = [
            'stripeInvoiceId' => $stripeInvoiceId,
            'stripeCustomerId' => $stripeCustomerId,
            'subscriptionId' => $subscriptionId,
            'payerId' => $payerId,
            'payerEmail' => $payerEmail,
            'receiperId' => $receiperId,
            'receiperEmail' => $receiperEmail,
            'amount' => $amount,
            'phlowFeePercent' => $phlowFeePercent,
            'currency' => $currency->value,
            'invoiceStatus' => $invoiceStatus->value,
            'frequency' => $frequency->value,
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeInvoicesTable::class,
            records: $invoice
        );
    }

}