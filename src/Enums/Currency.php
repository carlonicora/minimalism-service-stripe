<?php

namespace CarloNicora\Minimalism\Services\Stripe\Enums;


enum Currency: string
{
    // @see Stripe supported currencies https://stripe.com/docs/currencies
    case EUR = 'eur';
    case USD = 'usd';
    case GBP = 'gbp';

    /**
     * @return int
     */
    public function multiplier(): int
    {
        // Define how much cents in one currency unit ($/€/£). Can be 0 for zero-decimal currencies like ¥ (Japaneese yen)
        return match ($this) {
            self::EUR,
            self::USD,
            self::GBP => 100,
        };
    }

    /**
     * @return int
     */
    public function max(): int {
        return match ($this) {
            self::EUR,
            self::USD,
            self::GBP => 10000 * $this->multiplier()
            // @see https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
            // Stripe - 99999999 * $this->multiplier()
        };
    }

    /**
     * @return int
     */
    public function min(): int {
        // @see https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
        return match ($this) {
            self::EUR, // Stripe - 0.50€
            self::USD, // Stripe - 0.5$
            self::GBP => $this->multiplier() // Stripe - 0.30£
        };
    }

    /**
     * @return PaymentMethods[]
     */
    public function paymentMethods(): array
    {
        return match ($this) {
            self::EUR => [PaymentMethods::Card, PaymentMethods::SepaDebit],
            self::USD,
            self::GBP => [PaymentMethods::Card],

        };
    }
}