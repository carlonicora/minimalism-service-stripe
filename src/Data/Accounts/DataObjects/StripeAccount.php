<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
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

    /** @var bool */
    #[DbField]
    private bool $payoutsEnabled;

    /** @var string */
    #[DbField]
    private string $status;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return void
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
     * @return void
     */
    public function setUpdatedAt(
        int $updatedAt = null
    ): void
    {
        $this->updatedAt = $updatedAt;
    }

}