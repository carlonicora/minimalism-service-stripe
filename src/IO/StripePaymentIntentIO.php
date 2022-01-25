<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripePaymentIntentsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\PaymentIntentStatus;

class StripePaymentIntentIO extends AbstractLoader
{

    /**
     * @param int $id
     * @return array
     * @throws RecordNotFoundException
     */
    public function byId(
        int $id
    ): array
    {
        /** @see StripePaymentIntentsTable::byId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'byId',
            parameters: ['id' => $id]
        );

        return $this->returnSingleValue($result, recordType: 'Stripe payment intent');
    }

    /**
     * @param string $paymentIntentId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byStripePaymentIntentId(
        string $paymentIntentId
    ): array
    {
        /** @see StripePaymentIntentsTable::byStripePaymentIntentId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'byStripePaymentIntentId',
            parameters: ['paymentIntentId' => $paymentIntentId]
        );

        return $this->returnSingleValue($result, recordType: 'Stripe payment intent');
    }

    /**
     * @param string $paymentIntentId
     * @param string $stripeInvoiceId
     * @param int $payerId
     * @param string $payerEmail
     * @param int $recieperId
     * @param string $recieperAccountId
     * @param string $recieperEmail
     * @param int $amount
     * @param int $phlowFeeAmount
     * @param Currency $currency
     * @param PaymentIntentStatus $status
     * @return array
     */
    public function create(
        string              $paymentIntentId,
        string              $stripeInvoiceId,
        int                 $payerId,
        string              $payerEmail,
        int                 $recieperId,
        string              $recieperAccountId,
        string              $recieperEmail,
        int                 $amount,
        int                 $phlowFeeAmount,
        Currency            $currency,
        PaymentIntentStatus $status
    ): array
    {
        $payment = [
            'stripePaymentIntentId' => $paymentIntentId,
            'stripeInvoiceId' => $stripeInvoiceId,
            'payerId' => $payerId,
            'payerEmail' => $payerEmail,
            'recieperId' => $recieperId,
            'recieperAccountId' => $recieperAccountId,
            'recieperEmail' => $recieperEmail,
            'amount' => $amount,
            'phlowFeeAmount' => $phlowFeeAmount,
            'currency' => $currency->value,
            'status' => $status->value,
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
        /** @noinspection UnusedFunctionResultInspection */
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