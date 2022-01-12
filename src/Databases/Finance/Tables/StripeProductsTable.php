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
        'stripeProductId' => FieldInterface::STRING
            + FieldInterface::PRIMARY_KEY,
        'authorId' => FieldInterface::INTEGER,
        'name' => FieldInterface::STRING,
        'description' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $authorId
     * @return array
     * @throws Exception
     */
    public function byAuthorId(
        int $authorId,
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE authorId = ?';
        $this->parameters = ['i', $authorId];

        return $this->functions->runRead();
    }
}