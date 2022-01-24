<?php

namespace CarloNicora\Minimalism\Services\Stripe\Money;

use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;

class Amount
{

    /** @var bool */
    public const SKIP_MIN_VALIDATION = false;

    /**
     * @param int $integer
     * @param int $cents
     * @param Currency $currency
     */
    public function __construct(
        private int      $integer,
        private int      $cents = 0,
        private Currency $currency = Currency::EUR,
    )
    {
    }

    /**
     * @param int $amountInCents
     * @param string $currencyString
     * @return static
     */
    public static function construct(
        int $amountInCents,
        string $currencyString
    ): self
    {
        // TODO rename this method
        $currency = Currency::from($currencyString);
        $integer  = intdiv(num1: $amountInCents, num2: $currency->multiplier());

        return new Amount(integer: $integer, cents: $amountInCents - $integer * $currency->multiplier(), currency: $currency);
    }

    /**
     * @param int $amountInCents
     * @param string $currencyString
     * @return array
     */
    public static function fromCents(
        int    $amountInCents,
        string $currencyString,
    ): array
    {
        $currency = Currency::from($currencyString);
        $integer  = intdiv(num1: $amountInCents, num2: $currency->multiplier());

        return [$integer, $amountInCents - $integer * $currency->multiplier()];
    }

    /**
     * @return int
     */
    public function inCents(): int
    {
        return $this->integer * $this->currency->multiplier() + $this->cents;
    }

    /**
     * @return int
     */
    public function integer(): int
    {
        return $this->integer;
    }

    /**
     * @return int
     */
    public function cents(): int
    {
        return $this->cents;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->integer . '.' . $this->cents;
    }

    /**
     * @return Currency
     */
    public function currency(): Currency
    {
        return $this->currency;
    }

}