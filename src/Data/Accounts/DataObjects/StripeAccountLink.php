<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\DataObjects;

use CarloNicora\Minimalism\Services\ResourceBuilder\Interfaces\ResourceableDataInterface;

class StripeAccountLink implements ResourceableDataInterface
{
    /** @var string */
    private string $url;
    /** @var int */
    private int $createdAt;
    /** @var int */
    private int $expiresAt;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param int $expiresAt
     */
    public function setExpiresAt(int $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return 0;
    }
}