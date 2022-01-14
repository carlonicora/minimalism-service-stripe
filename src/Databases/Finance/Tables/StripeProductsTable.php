<?php

namespace CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripeProductsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeProducts';

    /** @var array */
    protected static array $fields = [
        'productId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'stripeProductId' => FieldInterface::STRING,
        'receiperId' => FieldInterface::INTEGER,
        'name' => FieldInterface::STRING,
        'description' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $receiperId
     * @return array
     * @throws Exception
     */
    public function byReceiperId(
        int $receiperId,
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE receiperId = ?';
        $this->parameters = ['i', $receiperId];

        return $this->functions->runRead();
    }
}