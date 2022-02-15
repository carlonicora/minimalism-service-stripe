<?php

namespace CarloNicora\Minimalism\Services\Stripe\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheBuilderFactory;
use CarloNicora\Minimalism\Services\DataMapper\Abstracts\AbstractLoader;
use CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables\StripeAccountsTable;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use Exception;

class StripeAccountIO extends AbstractLoader
{

    /**
     * @param int $userId
     * @return array
     * @throws MinimalismException
     * @throws Exception
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
            cacheBuilder: CacheBuilderFactory::create(
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
     * @throws Exception
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

        /** @noinspection UnusedFunctionResultInspection */
        $this->data->insert(
            tableInterfaceClassName: StripeAccountsTable::class,
            records: $account,
            cacheBuilder: CacheBuilderFactory::create(
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
        /** @noinspection UnusedFunctionResultInspection */
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
            CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $userId
            )
        );
    }
}