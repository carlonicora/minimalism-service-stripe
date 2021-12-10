<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentIntentsTable;

class StripePaymentIntentsDataReader extends AbstractLoader
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
}