<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentIntentsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;

class StripePaymentIntentsDataWriter extends AbstractLoader
{

    /**
     * @param string $paymentIntentId
     * @param int $payerId
     * @param string $payerEmail
     * @param int $receiperId
     * @param string $receiperAccountId
     * @param int $amount
     * @param int $phlowFeeAmount
     * @param string $currency
     * @param PaymentIntentStatus $status
     * @return array
     */
    public function create(
        string              $paymentIntentId,
        int                 $payerId,
        string              $payerEmail,
        int                 $receiperId,
        string              $receiperAccountId,
        int                 $amount,
        int                 $phlowFeeAmount,
        string              $currency,
        PaymentIntentStatus $status
    ): array
    {
        $payment = [
            'stripePaymentIntentId' => $paymentIntentId,
            'payerId' => $payerId,
            'payerEmail' => $payerEmail,
            'receiperId' => $receiperId,
            'receiperAccountId' => $receiperAccountId,
            'amount' => $amount,
            'phlowFeeAmount' => $phlowFeeAmount,
            'currency' => $currency,
            'status' => $status->value
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            records: $payment
        );
    }

    /**
     * @param string $paymentIntentId
     * @param PaymentIntentStatus $status
     */
    public function updateStatus(
        string              $paymentIntentId,
        PaymentIntentStatus $status,
    ): void
    {
        /** @see StripePaymentIntentsTable::updateStatus() */
        $this->data->run(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'paymentIntentId' => $paymentIntentId,
                'status' => $status->value,
            ],
        );
    }
}