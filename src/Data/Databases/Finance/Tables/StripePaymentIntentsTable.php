<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripePaymentIntentsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripePaymentIntents';

    /** @var array */
    protected static array $fields = [
        'paymentIntentId' => FieldInterface::STRING
            + FieldInterface::PRIMARY_KEY,
        'payerId' => FieldInterface::INTEGER,
        'payerEmail' => FieldInterface::STRING,
        'receiperId' => FieldInterface::INTEGER,
        'recеiperAccountId' => FieldInterface::STRING,
        'amount' => FieldInterface::INTEGER,
        'currency' => FieldInterface::STRING,
        'phlowFeeAmount' => FieldInterface::INTEGER,
        'status' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param string $paymentIntentId
     * @param string $status
     * @throws Exception
     */
    public function updateStatus(
        string $paymentIntentId,
        string $status,
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=? WHERE paymentIntentId=?;';

        $this->parameters = ['ss', $status, $paymentIntentId];

        $this->functions->runSql();
    }
}