<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Factories\Resources\StripeAccountsResourceFactory;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeAccountIO;
use CarloNicora\Minimalism\Services\Stripe\IO\StripeEventIO;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use JsonException;
use Stripe\Account;
use Stripe\Event;

class Accounts extends AbstractWebhook
{
    /** @var Event[] */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::ACCOUNT_UPDATED
    ];

    /**
     * @OA\Post(
     *     path="/webhooks/accounts",
     *     tags={"stripe"},
     *     summary="Webhook to manage Stripe accounts",
     *     operationId="webhookStripeAccounts",
     *     @OA\Response(
     *         response=201,
     *         description="created",
     *         @OA\JsonContent(ref="#/components/schemas/stripeAccount")
     *     ),
     *     @OA\Response(response=422, ref="#/components/responses/422"),
     *     @OA\Response(response=401, ref="#/components/responses/401"),
     *     @OA\Response(response=403, ref="#/components/responses/403"),
     *     @OA\Response(response=405, ref="#/components/responses/405"),
     *     @OA\Response(response=410, ref="#/components/responses/410"),
     *     @OA\Response(response=429, ref="#/components/responses/429")
     * )
     * @param Stripe $stripe
     * @param StripeEventIO $eventIO
     * @param StripeAccountsResourceFactory $accountsResourceFactory
     * @param StripeAccountIO $accountIO
     * @return int
     * @throws JsonException
     * @throws RecordNotFoundException
     */
    public function post(
        Stripe                        $stripe,
        StripeEventIO                 $eventIO,
        StripeAccountsResourceFactory $accountsResourceFactory,
        StripeAccountIO               $accountIO,
    ): int
    {
        /** @var Account $stripeAccount */
        $stripeAccount = self::processEvent(
            $stripe->getAccountWebhookSecret(),
            $eventIO
        );

        $localAccount = $accountIO->byStripeAccountId($stripeAccount->id);
        $userId       = $localAccount['userId'];
        $realStatus   = AccountStatus::calculate($stripeAccount);
        if ($localAccount['status'] !== $realStatus->value
            || (bool)$localAccount['payoutsEnabled'] !== $stripeAccount->payouts_enabled
        ) {
            $accountIO->updateAccountStatuses(
                userId: $userId,
                status: $realStatus,
                payoutsEnabled: $stripeAccount->payouts_enabled
            );

            if ($stripeAccount->payouts_enabled
                && ($realStatus === AccountStatus::Comlete || $realStatus === AccountStatus::Enabled)
            ) {
                $stripe->getOrCreateProduct(
                    artistId: $userId,
                );
            }
        }

        $this->document->addResource(
            $accountsResourceFactory->byUserId($userId)
        );

        return 201;
    }
}