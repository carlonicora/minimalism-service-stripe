<?php

namespace CarloNicora\Minimalism\Services\Stripe\Interfaces;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Stripe\Enums\SubscriptionFrequency;
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
     * @param int $recieperId
     * @param Amount $amount
     * @param Amount $phlowFee
     * @param string $payerEmail
     * @return Document
     */
    public function paymentIntent(
        int    $payerId,
        int    $recieperId,
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

    /**
     * @param int $payerId
     * @param int $recieperId
     * @param Amount $amount
     * @param int $phlowFeePercent
     * @param SubscriptionFrequency $frequency
     * @return Document
     */
    public function subscribe(
        int                   $payerId,
        int                   $recieperId,
        Amount                $amount,
        int                   $phlowFeePercent,
        SubscriptionFrequency $frequency = SubscriptionFrequency::Monthly
    ): Document;

    /**
     * @param int $recieperId
     * @param int $payerId
     * @return void
     * @throws ApiErrorException
     */
    public function cancelSubscription(
        int $recieperId,
        int $payerId,
    ): void;

    /**
     * @param int $recieperId
     * @param string $recieperStripeAccountId
     * @return array
     */
    public function getOrCreateProduct(
        int    $recieperId,
        string $recieperStripeAccountId
    ): array;

    /**
     * @param UserLoaderInterface $userService
     * @return void
     */
    public function setUserService(
        UserLoaderInterface $userService
    ): void;
}