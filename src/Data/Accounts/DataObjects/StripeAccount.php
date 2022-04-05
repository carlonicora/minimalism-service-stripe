<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Databases\StripeAccountsTable;

#[DbTable(tableClass: StripeAccountsTable::class)]
class StripeAccount implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripeAccountsTable::userId)]
    private int $id;

    /** @var string */
    #[DbField]
    private string $stripeAccountId;

    /** @var string */
    #[DbField]
    private string $email;

    /** @var string */
    #[DbField]
    private string $status;

    /** @var bool */
    #[DbField]
    private bool $payoutsEnabled;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $updatedAt;

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
    public function getStripeAccountId(): string
    {
        return $this->stripeAccountId;
    }

    /**
     * @param string $stripeAccountId
     */
    public function setStripeAccountId(string $stripeAccountId): void
    {
        $this->stripeAccountId = $stripeAccountId;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
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
     * @return bool
     */
    public function isPayoutsEnabled(): bool
    {
        return $this->payoutsEnabled;
    }

    /**
     * @param bool $payoutsEnabled
     */
    public function setPayoutsEnabled(bool $payoutsEnabled): void
    {
        $this->payoutsEnabled = $payoutsEnabled;
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
     * @return int
     */
    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    /**
     * @param int $updatedAt
     */
    public function setUpdatedAt(int $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

}