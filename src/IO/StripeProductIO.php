<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeProductsTable;
use Exception;

class StripeProductIO extends AbstractLoader
{

    /**
     * @param int $recieperId
     * @return array
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function byRecieperId(
        int $recieperId,
    ): array
    {
        /** @see StripeProductsTable::byRecieperId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeProductsTable::class,
            functionName: 'byRecieperId',
            parameters: [
                'recieperId' => $recieperId,
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe product');
    }

    /**
     * @param string $stripeProductId
     * @param int $recieperId
     * @param string $name
     * @param string $description
     * @return array
     */
    public function create(
        string $stripeProductId,
        int    $recieperId,
        string $name,
        string $description
    ): array
    {
        $records = [
            'stripeProductId' => $stripeProductId,
            'recieperId' => $recieperId,
            'name' => $name,
            'description' => $description,
            'createdAt' => date(format: 'Y-m-d H:i:s'),
        ];

        return $this->data->insert(
            tableInterfaceClassName: StripeProductsTable::class,
            records: $records
        );
    }
}