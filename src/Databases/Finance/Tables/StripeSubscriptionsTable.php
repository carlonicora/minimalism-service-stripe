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
            + FieldInterface::PRIMARY_KEY
            + FieldInterface::AUTO_INCREMENT,
        'stripeSubscriptionId' => FieldInterface::STRING,
        'stripeLastInvoiceId' => FieldInterface::STRING,
        'stripeLastPaymentIntentId' => FieldInterface::STRING,
        'stripePriceId' => FieldInterface::STRING,
        'productId' => FieldInterface::INTEGER,
        'payerId' => FieldInterface::INTEGER,
        'payerEmail' => FieldInterface::STRING,
        'recieperId' => FieldInterface::INTEGER,
        'recieperEmail' => FieldInterface::STRING,
        'frequency' => FieldInterface::STRING,
        'amount' => FieldInterface::INTEGER,
        'phlowFeePercent' => FieldInterface::INTEGER,
        'currency' => FieldInterface::STRING,
        'status' => FieldInterface::STRING,
        'currentPeriodEnd' => FieldInterface::STRING,
        'createdAt' => FieldInterface::STRING
            + FieldInterface::TIME_CREATE,
        'updatedAt' => FieldInterface::STRING
            + FieldInterface::TIME_UPDATE,
    ];

    /**
     * @param int $recieperId
     * @param int $payerId
     * @return array
     * @throws Exception
     */
    public function byRecieperAndPayerIds(
        int $recieperId,
        int $payerId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE recieperId = ? AND payerId = ?';
        $this->parameters = ['ii', $recieperId, $payerId];

        return $this->functions->runRead();
    }

    /**
     * @param int $payerId
     * @return array
     * @throws Exception
     */
    public function byPayerId(
        int $payerId
    ): array
    {
        $this->sql        = 'SELECT * FROM ' . static::getTableName()
            . ' WHERE payerId = ?';
        $this->parameters = ['i', $payerId];

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
        $this->parameters = ['s', $stripeSubscriptionId];

        return $this->functions->runRead();
    }

    /**
     * @param string $stripeSubscriptionId
     * @param string $status
     * @param string $lastInvoiceId
     * @param string $currentPeriodEnd
     * @return void
     * @throws Exception
     */
    public function updateDetails(
        string $stripeSubscriptionId,
        string $status,
        string $lastInvoiceId,
        string $currentPeriodEnd
    ): void
    {
        $this->sql = 'UPDATE ' . self::getTableName() . ' SET status=?, stripeLastInvoiceId=?, currentPeriodEnd=? WHERE stripeSubscriptionId=?;';

        $this->parameters = ['ssss', $status, $lastInvoiceId, $currentPeriodEnd, $stripeSubscriptionId];

        $this->functions->runSql();
    }

}