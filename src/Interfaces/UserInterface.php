<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

interface UserInterface
{

    /**
     * @param int $userId
     * @return void
     */
    public function load(int $userId): void;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @return string
     */
    public function getUserName(): string;

    /**
     * @return string|null
     */
    public function getUrl(): ?string;

    /**
     * @return string|null
     */
    public function getAvatar(): ?string;
}