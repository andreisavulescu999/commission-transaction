<?php

namespace App;

use App\Dto\OperationDto;
use App\Service\CommissionCalculator;
use App\Service\CurrencyConverter;
use App\Service\WeeklyWithdrawTracker;
use Exception;

class Bootstrap
{
    public const CSV_HEADER = ['date', 'userId', 'userType', 'type', 'amount', 'currency'];

    /**
     * @throws Exception
     */
    public function run(string $csvPath): array
    {
        $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $tracker = new WeeklyWithdrawTracker();
        $converter = new CurrencyConverter();
        $calculator = new CommissionCalculator($converter, $tracker);

        $results = [];

        foreach ($lines as $line) {
            try {
                $operationDto = OperationDto::fromCsvLine($line);
                $results[] = $calculator->handleDto($operationDto);
            } catch (Exception $e) {
                throw new Exception("Failed to parse or process line: $line. " . $e->getMessage());
            }
        }

        return $results;
    }
}