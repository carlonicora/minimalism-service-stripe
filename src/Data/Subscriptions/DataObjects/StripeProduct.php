<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Services\MySQL\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Subscriptions\Databases\StripeProductsTable;

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

    /** @var string */
    #[DbField]
    private string $description;

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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
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