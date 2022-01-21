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
     * @param string $stripeSubscriptionId
     * @return array
     * @throws Exception
     */
    public function byStripeSubscriptionId(
        string $stripeSubscriptionId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE stripeSubscriptionId = ?';
        $this->parameters = ['i', $stripeSubscriptionId];

        return $this->functions->runRead();
    }
}