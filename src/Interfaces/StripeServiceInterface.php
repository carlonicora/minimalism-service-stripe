<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;

interface StripeServiceInterface extends ServiceInterface
{
    /**
     * @param int $userId
     * @param string $email
     * @param string $refreshUrl
     * @param string $returnUrl
     * @return Document
     */
    public function connectAccount(
        int $userId,
        string $email,
        string $refreshUrl,
        string $returnUrl,
    ): Document;

    /**
     * @param int $payerId
     * @param int $receiperId
     * @param Amount $amount
     * @param Amount $phlowFee
     * @param string $receiptEmail
     * @return Document
     */
    public function paymentIntent(
        int    $payerId,
        int    $receiperId,
        Amount $amount,
        Amount $phlowFee,
        string $receiptEmail
    ): Document;

}