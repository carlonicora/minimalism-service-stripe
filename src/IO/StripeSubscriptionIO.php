<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripePaymentIntentsTable;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeSubscriptionsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionStatus;

class StripeSubscriptionIO extends AbstractLoader
{

    /**
     * @param int $subscriptionId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byId(
        int $subscriptionId
    ): array
    {
        /** @see StripeSubscriptionsTable::readById() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeSubscriptionsTable::class,
            functionName: 'readById',
            parameters: [
                'id' => $subscriptionId
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe subscription');
    }

    /**
     * @param int $receiperId
     * @param int $payerId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byReceiperAndPayerIds(
        int $receiperId,
        int $payerId
    ): array
    {
        /** @see StripeSubscriptionsTable::byReceiperAndPayerIds() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeSubscriptionsTable::class,
            functionName: 'byReceiperAndPayerIds',
            parameters: [
                'receiperId' => $receiperId,
                'payerId' => $payerId
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe subscription');
    }

    /**
     * @param string $stripeSubscriptionId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byStripeSubscriptionId(
        string $stripeSubscriptionId
    ): array
    {
        /** @see StripeSubscriptionsTable::byStripeSubscriptionId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeSubscriptionsTable::class,
            functionName: 'byStripeSubscriptionId',
            parameters: [
                'stripeSubscriptionId' => $stripeSubscriptionId,
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe subscription');
    }

    /**
     * @param int $payerId
     * @param string $payerEmail
     * @param int $receiperId
     * @param string $receiperEmail
     * @param string $stripeSubscriptionId
     * @param string $stripeLastPaymentIntentId
     * @param string $stripePriceId
     * @param int $productId
     * @param int $amount
     * @param int $phlowFeePercent
     * @param string $status
     * @param Currency $currency
     * @param SubscriptionFrequency $frequency
     * @return array
     */
    public function create(
        int                   $payerId,
        string                $payerEmail,
        int                   $receiperId,
        string                $receiperEmail,
        string                $stripeSubscriptionId,
        string                $stripeLastPaymentIntentId,
        string                $stripePriceId,
        int                   $productId,
        int                   $amount,
        int                   $phlowFeePercent,
        string                $status,
        Currency              $currency,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly
    ): array
    {
        $records = [
            'stripeSubscriptionId' => $stripeSubscriptionId,
            'stripeLastPaymentIntentId' => $stripeLastPaymentIntentId,
            'stripePriceId' => $stripePriceId,
            'receiperId' => $receiperId,
            'receiperEmail' => $receiperEmail,
            'productId' => $productId,
            'payerId' => $payerId,
            'payerEmail' => $payerEmail,
            'frequency' => $frequency->value,
            'amount' => $amount,
            'phlowFeePercent' => $phlowFeePercent,
            'status' => $status,
            'currency' => $currency->value,
            'createdAt' => date(format: 'Y-m-d H:i:s'),
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeSubscriptionsTable::class,
            records: $records
        );
    }

    /**
     * @param int $subscriptionId
     * @param SubscriptionStatus $status
     * @return void
     */
    public function updateStatus(
        int                $subscriptionId,
        SubscriptionStatus $status
    ): void
    {
        /** @see StripePaymentIntentsTable::updateStatus() */
        /** @noinspection UnusedFunctionResultInspection */
        $this->data->run(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'subscriptionId' => $subscriptionId,
                'status' => $status->value,
            ],
        );
    }

    /**
     * @param array $subscriptions
     * @return void
     */
    public function delete(
        array $subscriptions
    ): void
    {
        $this->data->delete(
            tableInterfaceClassName: StripeSubscriptionsTable::class,
            records: $subscriptions
        );
    }
}