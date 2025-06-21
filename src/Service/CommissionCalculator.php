<?php

namespace App\Service;

use App\Dto\OperationDto;
use App\Utils\Money;
use Exception;

class CommissionCalculator
{
    public const DEPOSIT = 'deposit';
    public const WITHDRAW = 'withdraw';
    public const BUSINESS = 'business';

    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly WeeklyWithdrawTracker $tracker
    ) {
    }

    /**
     * @throws Exception
     */
    public function handleDto(OperationDto $operation): string
    {
        switch ($operation->getType()) {
            case self::DEPOSIT:
                $fee = $operation->amount * 0.0003; // 0.03%
                break;
            case self::WITHDRAW:
                if ($operation->userType === self::BUSINESS) {
                    $fee = $operation->amount * 0.005; // 0.5%
                } else {
                    $fee = $this->handlePrivateWithdraw($operation);
                }
                break;
            default:
                throw new Exception("Unknown operation type");
        }

        return Money::roundUp($fee, $operation->currency);
    }

    /**
     * @throws Exception
     */
    private function handlePrivateWithdraw(OperationDto $op): float
    {
        $weekKey = $this->tracker->getWeekKey($op->getDate());
        $this->tracker->track($op->getUserId(), $weekKey);

        if ($this->tracker->isFreeOperation($op->getUserId(), $weekKey)) {
            $amountInEur = $this->converter->convertToEur($op->getAmount(), $op->getCurrency());
            $remainingFree = $this->tracker->getRemainingFreeAmount($op->getUserId(), $weekKey);

            $taxableInEur = max(0, $amountInEur - $remainingFree);
            $convertedTaxable = $this->converter->convertFromEur($taxableInEur, $op->getCurrency());

            $this->tracker->deductFreeAmount($op->getUserId(), $weekKey, $amountInEur);
            $fee = Money::calculatePercentage($convertedTaxable, 0.3);

            return (float)Money::roundUp($fee, $op->getCurrency());
        }

        $fee = Money::calculatePercentage($op->getAmount(), 0.3);

        return (float)Money::roundUp($fee, $op->getCurrency());
    }
}