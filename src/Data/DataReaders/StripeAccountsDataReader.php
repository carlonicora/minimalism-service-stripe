<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeAccountsTable;

class StripeAccountsDataReader extends AbstractLoader
{

    /**
     * @throws RecordNotFoundException
     */
    public function byUserId(
        int $userId
    ): array
    {
        /** @see StripeAccountsTable::byUserId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeAccountsTable::class,
            functionName: 'byUserId',
            parameters: ['userId' => $userId]
        );

        return $this->returnSingleValue($result, recordType: 'Stripe account');
    }
}