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
     * @param string $currency
     */
    public function create(
        int    $payerId,
        int    $receiperId,
        int    $amount,
        string $currency,
    ): void
    {
        $payment = [
            'payerId' => $payerId,
            'receiperId' => $receiperId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => PaymentStatus::Pending->value
        ];

        $this->data->insert(
            tableInterfaceClassName: StripePaymentsTable::class,
            records: $payment
        );
    }

    /**
     * @param int $paymentId
     * @param int $status
     */
    public function updatePaymentStatus(
        int $paymentId,
        int $status
    ): void
    {
        /** @see StripePaymentsTable::updateStatus() */
        $this->data->run(
            tableInterfaceClassName: StripePaymentsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'paymentId' => $paymentId,
                'status' => $status
            ],
        );

    }
}