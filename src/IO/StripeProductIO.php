<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeProductsTable;

class StripeProductIO extends AbstractLoader
{

    /**
     * @param int $receiperId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byReceiperId(
        int $receiperId,
    ): array
    {
        /** @see StripeProductsTable::byReceiperId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeProductsTable::class,
            functionName: 'byReceiperId',
            parameters: [
                'receiperId' => $receiperId,
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe product');
    }

    /**
     * @param string $stripeProductId
     * @param int $receiperId
     * @param string $name
     * @param string $description
     * @return array
     */
    public function create(
        string $stripeProductId,
        int    $receiperId,
        string $name,
        string $description
    ): array
    {
        $records = [
            'stripeProductId' => $stripeProductId,
            'receiperId' => $receiperId,
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