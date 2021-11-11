<?php

namespace CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks;

use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeAccountsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataReaders\StripeEventsDataReader;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeAccountsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Data\DataWriters\StripeEventsDataWriter;
use CarloNicora\Minimalism\Services\Stripe\Enums\AccountStatus;
use CarloNicora\Minimalism\Services\Stripe\Models\Stripe\Webhooks\Abstracts\AbstractWebhook;
use CarloNicora\Minimalism\Services\Stripe\Stripe;
use Stripe\Account;
use Stripe\Event;

class Accounts extends AbstractWebhook
{
    /** @var Event[] */
    protected const SUPPORTED_EVENT_TYPES = [
        Event::ACCOUNT_UPDATED
    ];

    /**
     * @param Stripe $stripe
     * @param StripeEventsDataReader $eventsDataReader
     * @param StripeEventsDataWriter $eventsDataWriter
     * @param StripeAccountsDataReader $accountsDataReader
     * @param StripeAccountsDataWriter $accountsDataWriter
     * @return int
     * @throws RecordNotFoundException
     */
    public function post(
        Stripe                   $stripe,
        StripeEventsDataReader   $eventsDataReader,
        StripeEventsDataWriter   $eventsDataWriter,
        StripeAccountsDataReader $accountsDataReader,
        StripeAccountsDataWriter $accountsDataWriter,
    ): int
    {
        /** @var Account $stripeAccount */
        $stripeAccount = $this->processEvent(
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
                status: $realStatus->value,
                payoutsEnabled: $stripeAccount->payouts_enabled
            );
        }

        return 201;
    }
}