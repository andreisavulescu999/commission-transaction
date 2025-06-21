<?php

namespace App\Utils;

class Money
{
    private const DEFUlT_PRECISION = 2;
    private const PRECISION = [
        CurrencyCodes::EUR => 2,
        CurrencyCodes::USD => 2,
        CurrencyCodes::JPY => 0,
    ];

    public static function calculatePercentage(float $amount, float $percent): float
    {
        return $amount * $percent / 100;
    }

    public static function roundUp(float $amount, string $currency): string
    {
        $precision = self::PRECISION[$currency] ?? self::DEFUlT_PRECISION;

        return number_format(ceil($amount * pow(10, $precision)) / pow(10, $precision), $precision, '.', '');
    }
}