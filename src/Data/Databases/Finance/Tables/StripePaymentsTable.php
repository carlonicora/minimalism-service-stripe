<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripePaymentsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripePayments';

    /** @var array */
    protected static array $fields = [
        'paymentId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'paymentIntentId' => FieldInterface::STRING,
        'payerId' => FieldInterface::INTEGER,
        'receiperId' => FieldInterface::INTEGER,
        'amount' => FieldInterface::INTEGER,
        'currency' => FieldInterface::STRING,
        'phlowFeeAmount' => FieldInterface::INTEGER,
        'status' => FieldInterface::INTEGER,
        'error' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $paymentId
     * @param int $status
     * @param string $paymentIntentId
     * @throws Exception
     */
    public function updateStatusAndIntentId(
        int $paymentId,
        int $status,
        string $paymentIntentId
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=?, paymentIntentId=? WHERE paymentId=?;';

        $this->parameters = ['isi', $status, $paymentIntentId, $paymentId];

        $this->functions->runSql();
    }
}