<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheBuilderFactory;
use CarloNicora\Minimalism\Services\MySQL\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Databases\StripeAccountsTable;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects\StripeAccount;

class StripeAccountIO extends AbstractSqlIO
{

    /**
     * @param int $userId
     * @return StripeAccount
     * @throws MinimalismException
     */
    public function byUserId(
        int $userId
    ): StripeAccount
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeAccountsTable::class)
                ->selectAll()
                ->addParameter(field: StripeAccountsTable::userId, value: $userId),
            cacheBuilder: CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $userId
            ),
            responseType: StripeAccount::class
        );
    }

    /**
     * @param string $email
     * @return array
     * @throws MinimalismException
     */
    public function byUserEmail(
        string $email
    ): array
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeAccountsTable::class)
                ->selectAll()
                ->addParameter(field: StripeAccountsTable::email, value: $email),
            responseType: StripeAccount::class
        );
    }

    /**
     * @param string $stripeAccountId
     * @return StripeAccount
     * @throws MinimalismException
     */
    public function byStripeAccountId(
        string $stripeAccountId
    ): StripeAccount
    {
        return $this->data->read(
            queryFactory: SqlQueryFactory::create(tableClass: StripeAccountsTable::class)
                ->selectAll()
                ->addParameter(field: StripeAccountsTable::stripeAccountId, value: $stripeAccountId),
            responseType: StripeAccount::class
        );
    }

    /**
     * @param StripeAccount $account
     */
    public function create(
        StripeAccount $account
    ): void
    {
        $this->data->create(
            queryFactory: $account,
            cacheBuilder: CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $account->getId()
            )
        );
    }

    /**
     * @param StripeAccount $account
     */
    public function update(
        StripeAccount $account
    ): void
    {
        $this->data->update(
            queryFactory: $account,
            cacheBuilder: CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $account->getId()
            )
        );

        $this->cache->invalidate(
            CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $account->getId()
            )
        );
    }
}