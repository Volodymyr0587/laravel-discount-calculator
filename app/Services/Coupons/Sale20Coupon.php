<?php

namespace App\Services\Coupons;

class Sale20Coupon implements CouponStrategy
{
    private const float DISCOUNT = 0.20;

    public function supports(?string $coupon): bool
    {
        return $coupon === 'SALE20';
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 2000
            ? $baseTotal * self::DISCOUNT
            : 0;
    }
}
