<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeProductsTable;

#[DbTable(tableClass: StripeProductsTable::class)]
class StripeProduct implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripeProductsTable::stripeProductId)]
    private int $id;

    /** @var string */
    #[DbField]
    private string $stripeProductId;

    /** @var int */
    #[DbField]
    private int $recieperId;

    /** @var string */
    #[DbField]
    private string $name;

    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var string|null */
    #[DbField]
    private string|null $description = null;

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
    public function getStripeProductId(): string
    {
        return $this->stripeProductId;
    }

    /**
     * @param string $stripeProductId
     */
    public function setStripeProductId(string $stripeProductId): void
    {
        $this->stripeProductId = $stripeProductId;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
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
     * @return string|null
     */
    public function getDescription(): string|null
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return void
     */
    public function setDescription(
        string|null $description = null
    ): void
    {
        $this->description = $description;
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