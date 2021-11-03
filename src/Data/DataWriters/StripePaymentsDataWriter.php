<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;


use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums\PaymentStatus;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentsTable;

class StripePaymentsDataWriter extends AbstractLoader
{

    /**
     * @param int $payerId
     * @param int $receiperId
     * @param int $amount
     * @param int $phlowFeeAmount
     * @param string $currency
     * @param PaymentStatus $status
     * @return array
     */
    public function create(
        int           $payerId,
        int           $receiperId,
        int           $amount,
        int           $phlowFeeAmount,
        string        $currency,
        PaymentStatus $status,
    ): array
    {
        $payment = [
            'payerId' => $payerId,
            'receiperId' => $receiperId,
            'amount' => $amount,
            'phlowFeeAmount' => $phlowFeeAmount,
            'currency' => $currency,
            'status' => $status->value,
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripePaymentsTable::class,
            records: $payment
        );
    }

    /**
     * @param int $paymentId
     * @param PaymentStatus $status
     * @param string $paymentIntentId
     */
    public function updatePaymentStatusAndIntentId(
        int $paymentId,
        PaymentStatus $status,
        string $paymentIntentId
    ): void
    {
        /** @see StripePaymentsTable::updateStatusAndIntentId() */
        $this->data->run(
            tableInterfaceClassName: StripePaymentsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'paymentId' => $paymentId,
                'status' => $status,
                'paymentIntentId' => $paymentIntentId
            ],
        );

    }
}