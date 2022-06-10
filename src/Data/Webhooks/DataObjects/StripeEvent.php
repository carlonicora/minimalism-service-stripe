<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\DataObjects;

use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbField;
use CarloNicora\Minimalism\Interfaces\Sql\Attributes\DbTable;
use CarloNicora\Minimalism\Interfaces\Sql\Enums\DbFieldType;
use CarloNicora\Minimalism\Interfaces\Sql\Interfaces\SqlDataObjectInterface;
use CarloNicora\Minimalism\Interfaces\Sql\Traits\SqlDataObjectTrait;
use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;
use CarloNicora\Minimalism\Services\Stripe\Data\Webhooks\Databases\StripeEventsTable;

#[DbTable(tableClass: StripeEventsTable::class)]
class StripeEvent implements SqlDataObjectInterface, ResourceableDataInterface
{
    use SqlDataObjectTrait;

    /** @var int */
    #[DbField(field: StripeEventsTable::eventId)]
    private int $id;

    /** @var string */
    #[DbField]

    private string $type;
    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var string|null */
    #[DbField]
    private string|null $relatedObjectId = null;

    /** @var string|null */
    #[DbField]
    private string|null $details = null;

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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
    public function getRelatedObjectId(): string|null
    {
        return $this->relatedObjectId;
    }

    /**
     * @param string|null $relatedObjectId
     * @return void
     */
    public function setRelatedObjectId(
        string $relatedObjectId = null
    ): void
    {
        $this->relatedObjectId = $relatedObjectId;
    }

    /**
     * @return string|null
     */
    public function getDetails(): string|null
    {
        return $this->details;
    }

    /**
     * @param string|null $details
     * @return void
     */
    public function setDetails(
        string $details = null
    ): void
    {
        $this->details = $details;
    }

}