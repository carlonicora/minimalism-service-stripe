<?php

namespace CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripePaymentIntentsTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripePaymentIntents';

    /** @var array */
    protected static array $fields = [
        'paymentIntentId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'stripePaymentIntentId' => FieldInterface::STRING,
        'stripeInvoiceId' => FieldInterface::STRING,
        'payerId' => FieldInterface::INTEGER,
        'payerEmail' => FieldInterface::STRING,
        'recieperId' => FieldInterface::INTEGER,
        'recieperAccountId' => FieldInterface::STRING,
        'recieperEmail' => FieldInterface::STRING,
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
     * @param string $stripePaymentIntentId
     * @return array
     * @throws Exception
     */
    public function byStripePaymentIntentId(
        string $stripePaymentIntentId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE stripePaymentIntentid = ? ';
        $this->parameters = ['s', $stripePaymentIntentId];

        return $this->functions->runRead();
    }

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
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=? WHERE stripePaymentIntentId=?;';

        $this->parameters = ['ss', $status, $paymentIntentId];

        $this->functions->runSql();
    }
}