<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

use CarloNicora\Minimalism\Interfaces\ServiceInterface;

interface StripePlatformInterface extends ServiceInterface
{

    /**
     * @return string
     */
    public function getRefreshUrlForAccountConnection(): string;

    /**
     * @return string
     */
    public function getReturnUrlForAccountConnection(): string;
}