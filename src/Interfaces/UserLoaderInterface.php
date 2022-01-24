<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

interface UserLoaderInterface
{

    /**
     * @param int $userId
     * @return UserInterface
     */
    public function load(int $userId): UserInterface;
}