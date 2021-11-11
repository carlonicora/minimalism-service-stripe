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
    public function byId(
        string $paymentIntentId
    ): array
    {
        /** @see StripePaymentIntentsTable::byId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripePaymentIntentsTable::class,
            functionName: 'byId',
            parameters: ['id' => $paymentIntentId]
        );

        return $this->returnSingleValue($result, recordType: 'Stripe payment intent');
    }
}