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
        'recieperId' => FieldInterface::INTEGER,
        'name' => FieldInterface::STRING,
        'description' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $recieperId
     * @return array
     * @throws Exception
     */
    public function byRecieperId(
        int $recieperId,
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE recieperId = ?';
        $this->parameters = ['i', $recieperId];

        return $this->functions->runRead();
    }
}