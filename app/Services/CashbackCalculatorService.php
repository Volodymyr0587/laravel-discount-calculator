<?php

namespace App\Services;

use App\Enums\UserType;

class CashbackCalculatorService
{
    private const CASHBACK_PERCENT = 2;

    public function calculate(float $finalTotal, UserType $userType): float
    {
        if ($userType !== UserType::VIP) {
            return 0;
        }

        return $finalTotal * (self::CASHBACK_PERCENT / 100);
    }
}
