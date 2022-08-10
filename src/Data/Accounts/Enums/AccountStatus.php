<?php

namespace CarloNicora\Minimalism\Services\Stripe\Data\Accounts\Enums;

use LogicException;
use Stripe\Account;

enum AccountStatus: string
{
    case Restricted = 'restricted';
    case RestrictedSoon = 'restricted_soon';
    case Pending = 'pending';
    case Enabled = 'enabled';
    case Complete = 'complete';
    case Rejected = 'rejected';

    /**
     * @param Account $account
     * @return static
     */
    public static function calculate(
        Account $account
    ): self
    {
        if ($account->requirements->offsetExists('disabled_reason') && $account->requirements->offsetGet('disabled_reason') !== null) {
            $disabledReason = DisabledReason::from($account->requirements->offsetGet('disabled_reason'));
            return $disabledReason->accountStatus();
        }

        if (!$account->payouts_enabled) {
            throw new LogicException(message: 'Connected account status with disabled payouts without disabled reason not implemented', code: 500);
        }

        $cardPaymentCapability = Capability::from($account->capabilities->offsetGet('card_payments'));

        if ($cardPaymentCapability === Capability::Active) {
            $futurePastDue         = $account->future_requirements->offsetGet('past_due');
            $futureCurentlyDue     = $account->future_requirements->offsetGet('currently_due');
            $futureEventuallyDue   = $account->future_requirements->offsetGet('eventually_due');
            $futureCurrentDeadline = $account->future_requirements->offsetGet('current_deadline');

            $noFutureRequirements = empty($futurePastDue) && empty($futureCurentlyDue) && empty($futureEventuallyDue) && $futureCurrentDeadline === null;

            $pastDue         = $account->requirements->offsetGet('past_due');
            $curentlyDue     = $account->requirements->offsetGet('currently_due');
            $eventuallyDue   = $account->requirements->offsetGet('eventually_due');
            $currentDeadline = $account->requirements->offsetGet('current_deadline');

            $noRequirements = empty($pastDue) && empty($curentlyDue) && empty($eventuallyDue) && $currentDeadline === null;

            if ($noRequirements && $noFutureRequirements) {
                return self::Complete;
            }

            if ($currentDeadline === null
                && ! empty($eventuallyDue)
                && empty($pastDue)
                && empty($curentlyDue)
            ) {
                return self::Enabled;
            }

            if ($currentDeadline !== null
                && ! empty($curentlyDue)
                && empty($pastDue)
            ) {
                return self::RestrictedSoon;
            }

            if ($currentDeadline !== null
                && empty($pastDue)
                && empty($curentlyDue)
            )
            {
                // No sense, but we had a real live example of a webhook with a deadline set without any addytional information.
                // In the Stripe dashboard it has the 'Complete' status, so we consider it as an undocumented complete status case
                return self::Complete;
            }

            if ($currentDeadline === null
                && ! empty($curentlyDue)
                && empty($pastDue)
            ) {
                // We had a real live example of a webhook with a current due without a current deadline
                // In the Stripe dashboard it has the 'Enabled' status, so we consider it as an undocumented enabled status case
                return self::Enabled;
            }

            throw new LogicException(message: 'Connected account status with enabled card payments not implemented', code: 500);
        }

        throw new LogicException(message: 'Connected account status not implemented', code: 500);
    }

    /**
     * @return bool
     */
    public function arePaymentsAllowed(): bool
    {
        return match ($this) {
            self::Restricted,
            self::Rejected,
            self::Enabled  => false,
            self::RestrictedSoon,
            self::Pending,
            self::Complete => true,
        };
    }

}