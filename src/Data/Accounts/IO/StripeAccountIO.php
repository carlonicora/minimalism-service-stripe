<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\IO;

use CarloNicora\Minimalism\Exceptions\MinimalismException;
use CarloNicora\Minimalism\Interfaces\Cache\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Abstracts\AbstractSqlIO;
use CarloNicora\Minimalism\Interfaces\Sql\Factories\SqlQueryFactory;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlQueryFactoryInterface;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheBuilderFactory;
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
     * @param StripeAccount|SqlDataObjectInterface|SqlQueryFactoryInterface|array $dataObject
     * @param CacheBuilderInterface|null $cache
     * @return SqlDataObjectInterface
     */
    public function create(
        StripeAccount|SqlDataObjectInterface|SqlQueryFactoryInterface|array $dataObject,
        ?CacheBuilderInterface $cache = null
    ): SqlDataObjectInterface
    {
        return $this->data->create(
            queryFactory: $dataObject,
            cacheBuilder: CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $dataObject->getId()
            )
        );
    }

    /**
     * @param StripeAccount|SqlDataObjectInterface|SqlQueryFactoryInterface|array $dataObject
     * @param CacheBuilderInterface|null $cache
     * @return void
     */
    public function update(
        StripeAccount|SqlDataObjectInterface|SqlQueryFactoryInterface|array $dataObject,
        ?CacheBuilderInterface                                                                                                $cache = null
    ): void
    {
        $this->data->update(
            queryFactory: $dataObject,
            cacheBuilder: CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $dataObject->getId()
            )
        );

        $this->cache->invalidate(
            CacheBuilderFactory::create(
                cacheName: 'stripeAccount',
                identifier: $dataObject->getId()
            )
        );
    }
}