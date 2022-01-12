<?php

namespace CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripeSubscriptionsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeSubscriptions';

    /** @var array */
    protected static array $fields = [
        'subscriptionId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY,
        'stripeSubscriptionId' => FieldInterface::STRING,
        'stripePriceId' => FieldInterface::STRING,
        'stripeProductId' => FieldInterface::STRING,
        'payerId' => FieldInterface::INTEGER,
        'payerEmail' => FieldInterface::STRING,
        'receiperId' => FieldInterface::INTEGER,
        'receiperEmail' => FieldInterface::STRING,
        'frequency' => FieldInterface::STRING,
        'amount' => FieldInterface::INTEGER,
        'phlowFeePercent' => FieldInterface::INTEGER,
        'currency' => FieldInterface::STRING,
        'status' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $receiperId
     * @param int $payerId
     * @return array
     * @throws Exception
     */
    public function byReceiperAndPayerIds(
        int $receiperId,
        int $payerId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE receiperId = ? AND payerId = ?';
        $this->parameters = ['ii', $receiperId, $payerId];

        return $this->functions->runRead();
    }
}