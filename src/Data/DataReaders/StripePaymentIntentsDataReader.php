<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripePaymentIntentsTable;

class StripePaymentIntentsDataReader extends AbstractLoader
{

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