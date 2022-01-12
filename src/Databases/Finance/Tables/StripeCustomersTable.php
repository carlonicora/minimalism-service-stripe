<?php

namespace CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripeCustomersTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeCustomers';

    /** @var array */
    protected static array $fields = [
        'userId' => FieldInterface::INTEGER + FieldInterface::PRIMARY_KEY,
        'stripeCustomerId' => FieldInterface::STRING,
        'email' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param string $customerId
     * @return array
     * @throws Exception
     */
    public function byCustomerId(
        string $customerId,
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE stripeCustomerId = ?';
        $this->parameters = ['s', $customerId];

        return $this->functions->runRead();
    }
}