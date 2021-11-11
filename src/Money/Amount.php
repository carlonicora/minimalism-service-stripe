<?php

namespace CarloNicora\Minimalism\Services\Stripe\Money;

use CarloNicora\Minimalism\Services\Stripe\Enums\Currency;
use RuntimeException;

class Amount
{

    /**
     * @param int $integer
     * @param int $cents
     * @param Currency $currency
     */
    public function __construct(
        private int $integer,
        private int $cents = 0,
        private Currency $currency =  Currency::EUR
    )
    {
        if ($this->inCents() > $this->currency->max()) {
            throw new RuntimeException(message: 'Payments greater than ' . $this->currency->max() / $this->currency->multiplier() . ' ' . $this->currency->value . ' are not allowed', code: 500);
        }

        if ($this->inCents() < $this->currency->min()) {
            throw new RuntimeException(message: 'Payments lower than ' . $this->currency->min() / $this->currency->multiplier() . ' ' . $this->currency->value . ' are not allowed', code: 500);
        }
    }

    /**
     * @return int
     */
    public function inCents(): int
    {
        return $this->integer * $this->currency->multiplier() + $this->cents;
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