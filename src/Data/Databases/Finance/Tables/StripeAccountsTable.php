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
        'accountId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'userId' => FieldInterface::INTEGER,
        'stripeAccountId' => FieldInterface::STRING,
        'email' => FieldInterface::STRING,
        'connectionStatus' => FieldInterface::INTEGER,
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
     * @param int $accountId
     * @param int $connectionStatus
     * @throws Exception
     */
    public function updateStatus(
        int $accountId,
        int $connectionStatus
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET connectionStatus=? WHERE accountId=?;';

        $this->parameters = ['ii', $accountId, $connectionStatus];

        $this->functions->runSql();
    }
}