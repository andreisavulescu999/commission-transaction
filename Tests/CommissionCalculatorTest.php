<?php

namespace Tests\Service;

use App\Dto\OperationDto;
use App\Service\CommissionCalculator;
use App\Service\CurrencyConverter;
use App\Service\WeeklyWithdrawTracker;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testDepositCommission(): void
    {
        $converter = $this->createMock(CurrencyConverter::class);
        $tracker = $this->createMock(WeeklyWithdrawTracker::class);
        $calculator = new CommissionCalculator($converter, $tracker);

        $dto = new OperationDto(
            date: '2025-06-21',
            userId: 1,
            userType: 'private',
            type: 'deposit',
            amount: 1000,
            currency: 'EUR'
        );

        $result = $calculator->handleDto($dto);
        $this->assertEquals('0.30', $result); // 1000 * 0.03% = 0.3
    }

    /**
     * @throws Exception
     */
    public function testBusinessWithdrawCommission(): void
    {
        $converter = $this->createMock(CurrencyConverter::class);
        $tracker = $this->createMock(WeeklyWithdrawTracker::class);
        $calculator = new CommissionCalculator($converter, $tracker);

        $dto = new OperationDto(
            date: '2025-06-21',
            userId: 2,
            userType: 'business',
            type: 'withdraw',
            amount: 1000,
            currency: 'EUR'
        );

        $result = $calculator->handleDto($dto);
        $this->assertEquals('5.00', $result); // 1000 * 0.5% = 5.0
    }

    /**
     * @throws Exception
     */
    public function testPrivateWithdrawWithinFreeLimit(): void
    {
        $converter = $this->createMock(CurrencyConverter::class);
        $tracker = $this->createMock(WeeklyWithdrawTracker::class);

        $dto = new OperationDto(
            date: '2025-06-21',
            userId: 3,
            userType: 'private',
            type: 'withdraw',
            amount: 500,
            currency: 'EUR'
        );

        $tracker->method('getWeekKey')->willReturn('2025-W25');
        $tracker->method('track');
        $tracker->method('isFreeOperation')->willReturn(true);
        $tracker->method('getRemainingFreeAmount')->willReturn(1000.0); // More than withdraw
        $tracker->method('deductFreeAmount');

        $converter->method('convertToEur')->willReturn(500.0);
        $converter->method('convertFromEur')->willReturn(0.0); // taxable is 0

        $calculator = new CommissionCalculator($converter, $tracker);
        $result = $calculator->handleDto($dto);

        $this->assertEquals('0.00', $result);
    }

    /**
     * @throws Exception
     */
    public function testPrivateWithdrawExceedingFreeLimit(): void
    {
        $converter = $this->createMock(CurrencyConverter::class);
        $tracker = $this->createMock(WeeklyWithdrawTracker::class);

        $dto = new OperationDto(
            date: '2025-06-21',
            userId: 3,
            userType: 'private',
            type: 'withdraw',
            amount: 1200,
            currency: 'EUR'
        );

        $tracker->method('getWeekKey')->willReturn('2025-W25');
        $tracker->method('track');
        $tracker->method('isFreeOperation')->willReturn(true);
        $tracker->method('getRemainingFreeAmount')->willReturn(1000.0);
        $tracker->method('deductFreeAmount');

        $converter->method('convertToEur')->willReturn(1200.0);
        $converter->method('convertFromEur')->willReturn(200.0);

        $calculator = new CommissionCalculator($converter, $tracker);
        $result = $calculator->handleDto($dto);

        $this->assertEquals('0.60', $result); // 0.3% of 200 = 0.6
    }
}
