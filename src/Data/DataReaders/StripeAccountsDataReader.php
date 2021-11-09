<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataReaders;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeAccountsTable;

class StripeAccountsDataReader extends AbstractLoader
{

    /**
     * @param int $userId
     * @return array
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

    /**
     * @param string $stripeAccountId
     * @return array
     * @throws RecordNotFoundException
     */
    public function byStripeAccountId(
        string $stripeAccountId
    ): array
    {
        /** @see StripeAccountsTable::byStripeAccountId() */
        $result = $this->data->read(
            tableInterfaceClassName: StripeAccountsTable::class,
            functionName: 'byStripeAccountId',
            parameters: ['stripeAccountId' => $stripeAccountId]
        );

        return $this->returnSingleValue($result, recordType: 'Stripe account');
    }
}