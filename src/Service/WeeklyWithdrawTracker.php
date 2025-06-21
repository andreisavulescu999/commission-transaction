<?php

namespace App\Service;

use DateTime;
use Exception;

class WeeklyWithdrawTracker
{
    public const DEFAULT_DATE = 'Y-m-d';
    private array $data = [];

    /**
     * @throws Exception
     */
    public function getWeekKey(string $date): string
    {
        $dt = DateTime::createFromFormat(self::DEFAULT_DATE, $date);

        return $dt->format('o-W');
    }

    public function track(int $userId, string $weekKey): void
    {
        if (!isset($this->data[$userId][$weekKey])) {
            $this->data[$userId][$weekKey] = [
                'count' => 0,
                'free_used' => 0.0
            ];
        }
        $this->data[$userId][$weekKey]['count']++;
    }

    public function isFreeOperation(int $userId, string $weekKey): bool
    {
        return $this->data[$userId][$weekKey]['count'] <= 3;
    }

    public function getRemainingFreeAmount(int $userId, string $weekKey): float
    {
        $used = $this->data[$userId][$weekKey]['free_used'];
        return max(0, 1000.00 - $used);
    }

    public function deductFreeAmount(int $userId, string $weekKey, float $eurAmount): void
    {
        $this->data[$userId][$weekKey]['free_used'] += $eurAmount;
    }
}