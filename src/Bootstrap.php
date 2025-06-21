<?php

namespace App;

use App\Dto\OperationDto;
use App\Service\CommissionCalculator;
use App\Service\CurrencyConverter;
use App\Service\WeeklyWithdrawTracker;
use Exception;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

        $encoders = [new CsvEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $results = [];

        foreach ($lines as $line) {
            try {
                $data = $serializer->decode($line, 'csv', ['csv_header' => self::CSV_HEADER]);
                $operationDto = $serializer->denormalize($data, OperationDto::class);
                $results[] = $calculator->handleDto($operationDto);
            } catch (NotEncodableValueException|Exception $e) {
                throw new Exception("Failed to parse or process line: $line. " . $e->getMessage());
            }
        }

        return $results;
    }
}