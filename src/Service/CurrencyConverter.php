<?php

namespace App\Service;

use App\Utils\CurrencyCodes;

class CurrencyConverter
{
    public const RATE_EUR = 1;
    public const RATE_USD = 1.1497;
    public const RATE_JPY = 129.53;

    private array $rates = [
        CurrencyCodes::EUR => self::RATE_EUR,
        CurrencyCodes::USD => self::RATE_USD,
        CurrencyCodes::JPY => self::RATE_JPY,
    ];

    public function convertToEur(float $amount, string $currency): float
    {
        return $amount / $this->rates[$currency];
    }

    public function convertFromEur(float $amount, string $currency): float
    {
        return $amount * $this->rates[$currency];
    }
}