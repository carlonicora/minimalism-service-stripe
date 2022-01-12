<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeAccountsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;

class StripeAccountIO extends AbstractLoader
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
            parameters: ['userId' => $userId],
            cacheBuilder: $this->cacheFactory->create(
                cacheName: 'stripeAccount',
                identifier: $userId
            )
        );

        return $this->returnSingleValue($result, recordType: 'Stripe account');
    }

    /**
     * @param string $email
     * @return array
     */
    public function byUserEmail(
        string $email
    ): array
    {
        /** @see StripeAccountsTable::byEmail() */
        return $this->data->read(
            tableInterfaceClassName: StripeAccountsTable::class,
            functionName: 'byEmail',
            parameters: ['email' => $email]
        );
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
            cacheBuilder: $this->cacheFactory->create(
                cacheName: 'stripeAccount',
                identifier: $userId
            )
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

        $this->cache->invalidate(
            $this->cacheFactory->create(
                cacheName: 'stripeAccount',
                identifier: $userId
            )
        );
    }
}