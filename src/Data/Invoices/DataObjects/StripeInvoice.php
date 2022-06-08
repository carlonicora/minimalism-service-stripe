<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Invoices\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Invoices\Databases\StripeInvoicesTable;

#[DbTable(tableClass: StripeInvoicesTable::class)]
class StripeInvoice implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripeInvoicesTable::invoiceId)]
    private int $id;

    /** @var string */
    #[DbField]
    private string $stripeInvoiceId;

    /** @var string */
    #[DbField]
    private string $stripeCustomerId;

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

    /** @var string */
    #[DbField]
    private string $frequency;

    /** @var int */
    #[DbField]
    private int $amount;

    /** @var int */
    #[DbField]
    private int $phlowFeePercent;

    /** @var string */
    #[DbField]
    private string $currency;

    /** @var string */
    #[DbField]
    private string $status;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int|null */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int|null $updatedAt = null;

    /** @var int|null */
    #[DbField]
    private int|null $subscriptionId = null;

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
     * @return string
     */
    public function getStripeInvoiceId(): string
    {
        return $this->stripeInvoiceId;
    }

    /**
     * @param string $stripeInvoiceId
     */
    public function setStripeInvoiceId(string $stripeInvoiceId): void
    {
        $this->stripeInvoiceId = $stripeInvoiceId;
    }

    /**
     * @return string
     */
    public function getStripeCustomerId(): string
    {
        return $this->stripeCustomerId;
    }

    /**
     * @param string $stripeCustomerId
     */
    public function setStripeCustomerId(string $stripeCustomerId): void
    {
        $this->stripeCustomerId = $stripeCustomerId;
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
    public function getUpdatedAt(): int|null
    {
        return $this->updatedAt;
    }

    /**
     * @param int|null $updatedAt
     */
    public function setUpdatedAt(
        int $updatedAt = null
    ): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return int|null
     */
    public function getSubscriptionId(): int|null
    {
        return $this->subscriptionId;
    }

    /**
     * @param int|null $subscriptionId
     */
    public function setSubscriptionId(
        int $subscriptionId = null
    ): void
    {
        $this->subscriptionId = $subscriptionId;
    }

}