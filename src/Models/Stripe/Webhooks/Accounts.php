<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Services\DataMapper\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeAccountsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeEventsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeAccountsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeEventsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\ResourceReaders\StripeAccountsResourceReader;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
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
     * @param StripeEventsDataReader $eventsDataReader
     * @param StripeEventsDataWriter $eventsDataWriter
     * @param StripeAccountsResourceReader $accountsResourceReader
     * @param StripeAccountsDataReader $accountsDataReader
     * @param StripeAccountsDataWriter $accountsDataWriter
     * @return int
     * @throws JsonException
     * @throws RecordNotFoundException
     */
    public function post(
        Stripe                       $stripe,
        StripeEventsDataReader       $eventsDataReader,
        StripeEventsDataWriter       $eventsDataWriter,
        StripeAccountsResourceReader $accountsResourceReader,
        StripeAccountsDataReader     $accountsDataReader,
        StripeAccountsDataWriter     $accountsDataWriter,
    ): int
    {
        /** @var Account $stripeAccount */
        $stripeAccount = self::processEvent(
            $stripe->getAccountWebhookSecret(),
            $eventsDataReader,
            $eventsDataWriter,
        );

        $localAccount = $accountsDataReader->byStripeAccountId($stripeAccount->id);
        $realStatus   = AccountStatus::calculate($stripeAccount);
        if ($localAccount['status'] !== $realStatus->value
            || (bool)$localAccount['payoutsEnabled'] !== $stripeAccount->payouts_enabled
        ) {
            $accountsDataWriter->updateAccountStatuses(
                userId: $localAccount['userId'],
                status: $realStatus,
                payoutsEnabled: $stripeAccount->payouts_enabled
            );
        }

        $this->document->addResource(
            $accountsResourceReader->byUserId($localAccount['userId'])
        );

        return 201;
    }
}