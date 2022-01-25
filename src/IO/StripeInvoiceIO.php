<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeInvoicesTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\InvoiceStatus;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;

class StripeInvoiceIO extends AbstractLoader
{

    /**
     * @param string $stripeInvoiceId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byStripeInvoiceId(
        string $stripeInvoiceId
    ): array
    {
        /** @see StripeInvoicesTable::readById() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeInvoicesTable::class,
            functionName: 'readById',
            parameters: [
                'id' => $stripeInvoiceId
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe customer');
    }

    /**
     * @param string $stripeInvoiceId
     * @param string $stripeCustomerId
     * @param int $payerId
     * @param string $payerEmail
     * @param int $recieperId
     * @param string $recieperEmail
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
        int                   $recieperId,
        string                $recieperEmail,
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
            'recieperId' => $recieperId,
            'recieperEmail' => $recieperEmail,
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

    /**
     * @param int $invoiceId
     * @param InvoiceStatus $status
     * @return void
     */
    public function updateStatus(
        int $invoiceId,
        InvoiceStatus $status
    ): void
    {
        /** @see StripeInvoicesTable::updateStatus() */
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: StripeInvoicesTable::class,
            functionName: 'updateStatus',
            parameters: [
                'invoiceId' => $invoiceId,
                'status' => $status->value,
            ],
        );
    }

}