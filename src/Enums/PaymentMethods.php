<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;

enum PaymentMethods: string
{
    case Card = 'card';
    case SepaDebit = 'sepa_debit';

    /**
     * @return Country[]
     */
    public function supportedCountries(): array
    {
        return match ($this) {
            self::Card      => Country::cases(),
            self::SepaDebit => [
                Country::Austria,
                Country::Belgium,
                Country::Bulgaria,
                Country::Croatia,
                Country::Cyprus,
                Country::CzechRepublic,
                Country::Denmark,
                Country::Estonia,
                Country::Finland,
                Country::France,
                Country::Germany,
                Country::Greece,
                Country::Hungary,
                Country::Iceland,
                Country::Ireland,
                Country::Italy,
                Country::Latvia,
                Country::Liechtenstein,
                Country::Lithuania,
                Country::Luxembourg,
                Country::Malta,
                Country::Monaco,
                Country::Netherlands,
                Country::Norway,
                Country::Poland,
                Country::Portugal,
                Country::Romania,
                Country::SanMarino,
                Country::Slovakia,
                Country::Slovenia,
                Country::Spain,
                Country::Sweden,
                Country::Switzerland,
                Country::UnitedKingdom,
            ],
        };
    }
}