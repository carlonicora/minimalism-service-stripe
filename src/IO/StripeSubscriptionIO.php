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
     * @param int $payerId
     * @param string $stripeSubscriptionId
     * @param string $stripePriceId
     * @param int $stripeProductId
     * @param int $amount
     * @param int $phlowFeePercent
     * @param Currency $currency
     * @param SubscriptionFrequency $frequency
     * @return array
     */
    public function create(
        int                   $payerId,
        string                $stripeSubscriptionId,
        string                $stripePriceId,
        int                   $stripeProductId,
        int                   $amount,
        int                   $phlowFeePercent,
        Currency              $currency,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly
    ): array
    {
        $records = [
            'stripeSubscriptionId' => $stripeSubscriptionId,
            'stripePriceId' => $stripePriceId,
            'stripeProductId' => $stripeProductId,
            'payerId' => $payerId,
            'frequency' => $frequency->value,
            'amount' => $amount,
            'phlowFeePercent' => $phlowFeePercent,
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
        $this->data->run(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'subscriptionId' => $subscriptionId,
                'status' => $status->value,
            ],
        );
    }
}