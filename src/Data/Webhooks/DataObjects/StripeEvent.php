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

    /** @var string */
    #[DbField]
    private string $eventId;

    /** @var string */
    #[DbField]

    private string $type;
    /** @var int */
    #[DbField(fieldType: DbFieldType::IntDateTime)]
    private int $createdAt;

    /** @var string|null */
    #[DbField]
    private ?string $relatedObjectId = null;

    /** @var string|null */
    #[DbField]
    private ?string $details = null;

    /** @var int */
    #[DbField]
    private int $isProcessed = 0;

    /**
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }

    /**
     * @param string $eventId
     * @return void
     */
    public function setEventId(string $eventId): void
    {
        $this->eventId = $eventId;
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
    public function getRelatedObjectId(): ?string
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
    public function getDetails(): ?string
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

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return (bool)$this->isProcessed;
    }

    /**
     * @param bool $isProcessed
     */
    public function setIsProcessed(
        bool $isProcessed
    ): void
    {
        $this->isProcessed = (int)$isProcessed;
    }

    /**
     * @deprecated id is a string for stripe events
     * @see self::getId
     * @return int
     */
    public function getId(): int
    {
        return 0;
    }
}