<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeSubscriptionsTable;

#[DbTable(tableClass: StripeSubscriptionsTable::class)]
class StripeSubscription implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripeSubscriptionsTable::stripeSubscriptionId)]
    private int $id;

    /** @var string */
    #[DbField]
    private string $stripeSubscriptionId;

    /** @var string */
    #[DbField]
    private string $stripeLastInvoiceId;

    /** @var string */
    #[DbField]
    private string $stripeLastPaymentIntentId;

    /** @var string */
    #[DbField]
    private string $stripePriceId;

    /** @var int */
    #[DbField]
    private int $productId;

    /** @var int */
    #[DbField]
    private int $payerId;

    /** @var string */
    #[DbField]
    private string $payerEmail;

    /** @var int */
    #[DbField]
    private int $recieperId;

    /** @var string */
    #[DbField]
    private string $recieperEmail;

    /** @var int */
    #[DbField]
    private int $amount;

    /** @var int */
    #[DbField]
    private int $phlowFeePercent;

    /** @var string */
    #[DbField]
    private string $status;

    /** @var string */
    #[DbField]
    private string $currency;

    /** @var string */
    #[DbField]
    private string $frequency;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int|null */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int|null $currentPeriodEnd = null;

    /** @var int|null */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int|null $updatedAt = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPayerId(): int
    {
        return $this->payerId;
    }

    /**
     * @param int $payerId
     */
    public function setPayerId(int $payerId): void
    {
        $this->payerId = $payerId;
    }

    /**
     * @return string
     */
    public function getPayerEmail(): string
    {
        return $this->payerEmail;
    }

    /**
     * @param string $payerEmail
     */
    public function setPayerEmail(string $payerEmail): void
    {
        $this->payerEmail = $payerEmail;
    }

    /**
     * @return int
     */
    public function getRecieperId(): int
    {
        return $this->recieperId;
    }

    /**
     * @param int $recieperId
     */
    public function setRecieperId(int $recieperId): void
    {
        $this->recieperId = $recieperId;
    }

    /**
     * @return string
     */
    public function getRecieperEmail(): string
    {
        return $this->recieperEmail;
    }

    /**
     * @param string $recieperEmail
     */
    public function setRecieperEmail(string $recieperEmail): void
    {
        $this->recieperEmail = $recieperEmail;
    }

    /**
     * @return string
     */
    public function getStripeSubscriptionId(): string
    {
        return $this->stripeSubscriptionId;
    }

    /**
     * @param string $stripeSubscriptionId
     */
    public function setStripeSubscriptionId(string $stripeSubscriptionId): void
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
    }

    /**
     * @return string
     */
    public function getStripeLastInvoiceId(): string
    {
        return $this->stripeLastInvoiceId;
    }

    /**
     * @param string $stripeLastInvoiceId
     */
    public function setStripeLastInvoiceId(string $stripeLastInvoiceId): void
    {
        $this->stripeLastInvoiceId = $stripeLastInvoiceId;
    }

    /**
     * @return string
     */
    public function getStripeLastPaymentIntentId(): string
    {
        return $this->stripeLastPaymentIntentId;
    }

    /**
     * @param string $stripeLastPaymentIntentId
     */
    public function setStripeLastPaymentIntentId(string $stripeLastPaymentIntentId): void
    {
        $this->stripeLastPaymentIntentId = $stripeLastPaymentIntentId;
    }

    /**
     * @return string
     */
    public function getStripePriceId(): string
    {
        return $this->stripePriceId;
    }

    /**
     * @param string $stripePriceId
     */
    public function setStripePriceId(string $stripePriceId): void
    {
        $this->stripePriceId = $stripePriceId;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getPhlowFeePercent(): int
    {
        return $this->phlowFeePercent;
    }

    /**
     * @param int $phlowFeePercent
     */
    public function setPhlowFeePercent(int $phlowFeePercent): void
    {
        $this->phlowFeePercent = $phlowFeePercent;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }


    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getFrequency(): string
    {
        return $this->frequency;
    }

    /**
     * @param string $frequency
     */
    public function setFrequency(string $frequency): void
    {
        $this->frequency = $frequency;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int|null
     */
    public function getCurrentPeriodEnd(): int|null
    {
        return $this->currentPeriodEnd;
    }

    /**
     * @param int|null $currentPeriodEnd
     * @return void
     */
    public function setCurrentPeriodEnd(
        int $currentPeriodEnd = null
    ): void
    {
        $this->currentPeriodEnd = $currentPeriodEnd;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt(): int|null
    {
        return $this->updatedAt;
    }

    /**
     * @param int|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(
        int $updatedAt = null
    ): void
    {
        $this->updatedAt = $updatedAt;
    }

}