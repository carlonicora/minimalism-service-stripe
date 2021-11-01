<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\Enums\AccountConnectionStatus;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeAccountsTable;

class StripeAccountsDataWriter extends AbstractLoader
{

    /**
     * @param int $userId
     * @param string $stripeAccountId
     * @param string $email
     * @param AccountConnectionStatus $connectionStatus
     */
    public function create(
        int                     $userId,
        string                  $stripeAccountId,
        string                  $email,
        AccountConnectionStatus $connectionStatus
    ): void
    {
        $account = [
            'userId' => $userId,
            'stripeAccountId' => $stripeAccountId,
            'email' => $email,
            'connectionStatus' => $connectionStatus->value
        ];

        $this->data->insert(
            tableInterfaceClassName: StripeAccountsTable::class,
            records: $account,
        );
    }

    /**
     * @param int $accountId
     * @param int $connectionStatus
     */
    public function updateConnectionStatus(
        int $accountId,
        int $connectionStatus
    ): void
    {
        /** @see StripeAccountsTable::updateStatus() */
        $this->data->run(
            tableInterfaceClassName: StripeAccountsTable::class,
            functionName: 'updateStatus',
            parameters: [
                'accountId' => $accountId,
                'connectionStatus' => $connectionStatus
            ],
        );

    }
}