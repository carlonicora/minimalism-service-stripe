<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\PaymentIntents\Databases\StripePaymentIntentsTable;

class StripePaymentIntent implements SqlDataObjectInterface, ResourceableDataInterface
{

    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripePaymentIntentsTable::paymentIntentId)]
    private int $id;

    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::stripePaymentIntentId)]
    private string $stripePaymentIntentId;
    /** @var int */
    #[DbField(field: StripePaymentIntentsTable::payerId)]
    private int $payerId;

    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::payerEmail)]
    private string $payerEmail;

    /** @var int */
    #[DbField(field: StripePaymentIntentsTable::recieperId)]
    private int $recieperId;

    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::recieperAccountId)]
    private string $recieperAccountId;

    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::recieperEmail)]
    private string $recieperEmail;

    /** @var int */
    #[DbField(field: StripePaymentIntentsTable::amount)]
    private int $amount;

    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::currency)]
    private string $currency;

    /** @var int */
    #[DbField(field: StripePaymentIntentsTable::phlowFeeAmount)]
    private int $phlowFeeAmount;
    /** @var string */
    #[DbField(field: StripePaymentIntentsTable::status)]
    private string $status;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int|null */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int|null $updatedAt = null;

    /** @var string|null */
    #[DbField(field: StripePaymentIntentsTable::stripeInvoiceId)]
    private string|null $stripeInvoiceId = null;

    /** @var string|null */
    private string|null $clientSecret = null;

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
    public function getStripePaymentIntentId(): string
    {
        return $this->stripePaymentIntentId;
    }

    /**
     * @param string $stripePaymentIntentId
     */
    public function setStripePaymentIntentId(string $stripePaymentIntentId): void
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;
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
    public function getRecieperAccountId(): string
    {
        return $this->recieperAccountId;
    }

    /**
     * @param string $recieperAccountId
     */
    public function setRecieperAccountId(string $recieperAccountId): void
    {
        $this->recieperAccountId = $recieperAccountId;
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
     * @return int
     */
    public function getPhlowFeeAmount(): int
    {
        return $this->phlowFeeAmount;
    }

    /**
     * @param int $phlowFeeAmount
     */
    public function setPhlowFeeAmount(int $phlowFeeAmount): void
    {
        $this->phlowFeeAmount = $phlowFeeAmount;
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
     * @return string|null
     */
    public function getStripeInvoiceId(): string|null
    {
        return $this->stripeInvoiceId;
    }

    /**
     * @param string|null $stripeInvoiceId
     */
    public function setStripeInvoiceId(
        string $stripeInvoiceId = null
    ): void
    {
        $this->stripeInvoiceId = $stripeInvoiceId;
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): string|null
    {
        return $this->clientSecret;
    }

    /**
     * @param string|null $clientSecret
     * @return void
     */
    public function setClientSecret(
        string $clientSecret = null
    ): void
    {
        $this->clientSecret = $clientSecret;
    }

}