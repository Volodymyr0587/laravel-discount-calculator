<?php

namespace App\Services;

class TaxCalculatorService
{
    private const int VAT_PERCENT = 20;

    public function calculate(float $baseTotal, float $discount): float
    {
        $taxableAmount = max($baseTotal - $discount, 0);

        return $taxableAmount * (self::VAT_PERCENT / 100);
    }
}
