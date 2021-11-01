<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;

interface StripeServiceInterface extends ServiceInterface
{
    /**
     * @param int $userId
     * @param string $email
     * @return Document
     */
    public function connectAccount(
        int $userId,
        string $email,
    ): Document;
}