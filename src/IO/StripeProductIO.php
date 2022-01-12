<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeProductsTable;

class StripeProductIO extends AbstractLoader
{

    /**
     * @param int $authorId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byAuthorId(
        int $authorId,
    ): array
    {
        /** @see StripeProductsTable::byAuthorId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeProductsTable::class,
            functionName: 'byAuthorId',
            parameters: [
                'authorId' => $authorId,
            ],
        );

        return $this->returnSingleValue($result, recordType: 'Stripe product');
    }

    /**
     * @param string $stripeProductId
     * @param int $userId
     * @param string $name
     * @param string $description
     * @return array
     */
    public function create(
        string $stripeProductId,
        int    $userId,
        string $name,
        string $description
    ): array
    {
        $records = [
            'stripeProductId' => $stripeProductId,
            'userId' => $userId,
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