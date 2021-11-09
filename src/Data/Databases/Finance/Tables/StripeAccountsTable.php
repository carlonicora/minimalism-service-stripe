<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripeAccountsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeAccounts';

    /** @var array */
    protected static array $fields = [
        'userId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY,
        'stripeAccountId' => FieldInterface::STRING,
        'email' => FieldInterface::STRING,
        'status' => FieldInterface::STRING,
        'payoutsEnabled' => FieldInterface::INTEGER,
        'error' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function byUserId(
        int $userId
    ): array
    {
        $this->sql = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE userId = ? ';
        $this->parameters = ['i', $userId];

        return $this->functions->runRead();
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
        $this->sql = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE stripeAccountId = ? ';
        $this->parameters = ['s', $stripeAccountId];

        return $this->functions->runRead();
    }

    /**
     * @param int $userId
     * @param string $status
     * @param bool $payoutsEnabled
     * @throws Exception
     */
    public function updateStatuses(
        int    $userId,
        string $status,
        bool   $payoutsEnabled
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=?, payoutsEnabled=? WHERE userId=?;';

        $this->parameters = ['sii', $status, $payoutsEnabled, $userId];

        $this->functions->runSql();
    }
}