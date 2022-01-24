<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

interface UserInterface
{

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