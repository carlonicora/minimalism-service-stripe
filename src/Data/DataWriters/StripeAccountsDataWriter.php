<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\DataWriters;

use CarloNicora\Minimalism\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables\StripeAccountsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;

class StripeAccountsDataWriter extends AbstractLoader
{

    /**
     * @param int $userId
     * @param string $stripeAccountId
     * @param string $email
     * @param AccountStatus $status
     * @param bool $payoutsEnabled
     */
    public function create(
        int           $userId,
        string        $stripeAccountId,
        string        $email,
        AccountStatus $status,
        bool          $payoutsEnabled
    ): void
    {
        $account = [
            'userId' => $userId,
            'stripeAccountId' => $stripeAccountId,
            'email' => $email,
            'status' => $status->value,
            'payoutsEnabled' => $payoutsEnabled
        ];

        $this->data->insert(
            tableInterfaceClassName: StripeAccountsTable::class,
            records: $account,
        );
    }

    /**
     * @param int $userId
     * @param AccountStatus $status
     * @param bool $payoutsEnabled
     */
    public function updateAccountStatuses(
        int           $userId,
        AccountStatus $status,
        bool          $payoutsEnabled
    ): void
    {
        /** @see StripeAccountsTable::updateStatuses() */
        $this->data->run(
            tableInterfaceClassName: StripeAccountsTable::class,
            functionName: 'updateStatuses',
            parameters: [
                'userId' => $userId,
                'status' => $status->value,
                'payoutsEnabled' => $payoutsEnabled
            ],
        );
    }
}