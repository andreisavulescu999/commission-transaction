<?php

namespace App\Dto;

use InvalidArgumentException;

class OperationDto
{
    public string $date;
    public int $userId;
    public string $userType;   // 'private' or 'business'
    public string $type;       // 'deposit' or 'withdraw'
    public float $amount;
    public string $currency;

    public function __construct(
        string $date,
        int $userId,
        string $userType,
        string $type,
        float $amount,
        string $currency
    ) {
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromCsvLine(string $line): self
    {
        $parts = str_getcsv($line);
        if (count($parts) !== 6) {
            throw new InvalidArgumentException("Invalid CSV line, expected 6 fields: $line");
        }

        [$date, $userId, $userType, $type, $amount, $currency] = $parts;

        // Validate date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            throw new InvalidArgumentException("Invalid date format: $date");
        }

        // Validate userId is positive integer
        if (!ctype_digit($userId) || (int)$userId <= 0) {
            throw new InvalidArgumentException("Invalid userId (must be positive integer): $userId");
        }

        // Validate userType (assuming 'private' or 'business')
        if (!in_array($userType, ['private', 'business'], true)) {
            throw new InvalidArgumentException("Invalid userType (expected 'private' or 'business'): $userType");
        }

        // Validate type (assuming 'deposit' or 'withdraw')
        if (!in_array($type, ['deposit', 'withdraw'], true)) {
            throw new InvalidArgumentException("Invalid operation type (expected 'deposit' or 'withdraw'): $type");
        }

        // Validate amount is a positive float
        if (!is_numeric($amount) || (float)$amount <= 0) {
            throw new InvalidArgumentException("Invalid amount (must be positive number): $amount");
        }

        // Validate currency is a 3-letter uppercase string (ISO 4217 code)
        if (!preg_match('/^[A-Z]{3}$/', strtoupper($currency))) {
            throw new InvalidArgumentException("Invalid currency code: $currency");
        }

        return new self(
            $date,
            (int)$userId,
            $userType,
            $type,
            (float)$amount,
            strtoupper($currency)
        );
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}