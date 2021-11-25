<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;

enum DisabledReason: string
{
    case RequirementsPastDue = 'requirements.past_due';
    case RequirementsPendingVerification = 'requirements.pending_verification';
    case Listed = 'listed';
    case PlatformPaused = 'platform_paused';
    case RejectedFraud = 'rejected.fraud';
    case RejectedListed = 'rejected.listed';
    case RejectedTermsOfService = 'rejected.terms_of_service';
    case RejectedOther = 'rejected.other';
    case UnderReview = 'under_review';
    case Other = 'other';

    /**
     * @return AccountStatus
     */
    public function accountStatus(): AccountStatus
    {
        return match ($this) {
            self::RequirementsPastDue => AccountStatus::Restricted,
            self::RequirementsPendingVerification,
            self::Listed,
            self::PlatformPaused,
            self::Other,
            self::UnderReview         => AccountStatus::Pending,
            self::RejectedFraud,
            self::RejectedListed,
            self::RejectedTermsOfService,
            self::RejectedOther       => AccountStatus::Rejected
        };
    }
}