<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Money\Amount;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;

interface StripeServiceInterface extends ServiceInterface
{

    public const REFRESH_URL = '/stripe/accounts/refresh';
    public const RETURN_URL = '/stripe/accounts/return';

    /**
     * @param int $userId
     * @param string $email
     * @return Account
     * @throws ApiErrorException
     */
    public function connectAccount(
        int    $userId,
        string $email,
    ): Account;

    /**
     * @param string $accountId
     * @param string $refreshUrl
     * @param string $returnUrl
     * @return Document
     * @throws ApiErrorException
     */
    public function createAccountOnboardingLink(
        string $accountId,
        string $refreshUrl,
        string $returnUrl
    ): Document;

    /**
     * @param int $payerId
     * @param int $receiperId
     * @param Amount $amount
     * @param Amount $phlowFee
     * @param string $payerEmail
     * @return Document
     */
    public function paymentIntent(
        int    $payerId,
        int    $receiperId,
        Amount $amount,
        Amount $phlowFee,
        string $payerEmail,
    ): Document;

    /**
     * @return string
     */
    public function getAccountWebhookSecret(): string;

    /**
     * @return string
     */
    public function getPaymentsWebhookSecret(): string;

    /**
     * @param int $userId
     * @return array
     */
    public function getAccountStatuses(int $userId): array;

}