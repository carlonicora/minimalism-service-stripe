<?php

namespace CarloNicora\Minimalism\Services\Stripe\Databases\Finance\Tables;

use CarloNicora\Minimalism\Services\MySQL\Abstracts\AbstractMySqlTable;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\FieldInterface;
use Exception;

class StripeInvoicesTable extends AbstractMySqlTable
{
    /** @var string */
    protected static string $tableName = 'stripeInvoices';

    /** @var array */
    protected static array $fields = [
        'invoiceId' => FieldInterface::INTEGER
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'stripeInvoiceId' => FieldInterface::STRING,
        'stripeCustomerId' => FieldInterface::STRING,
        'subscriptionId' => FieldInterface::INTEGER,
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

    /**
     * @param string $stripeInvoiceId
     * @return array
     * @throws Exception
     */
    public function byStripeInvoiceId(
        string $stripeInvoiceId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE stripeInvoiceId = ?';
        $this->parameters = ['s', $stripeInvoiceId];

        return $this->functions->runRead();
    }

    /**
     * @param int $invoiceId
     * @param string $status
     * @return void
     * @throws Exception
     */
    public function updateStatus(
        int $invoiceId,
        string $status
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=? WHERE invoiceId=?;';

        $this->parameters = ['si', $status, $invoiceId];

        $this->functions->runSql();
    }
}