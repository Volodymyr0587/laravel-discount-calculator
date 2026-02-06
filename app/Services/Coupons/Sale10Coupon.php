<?php

namespace App\Services\Coupons;

class Sale10Coupon implements CouponStrategy
{
    private const float DISCOUNT = 0.10;

    public function supports(?string $coupon): bool
    {
        return $coupon === 'SALE10';
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 1000
            ? $baseTotal * self::DISCOUNT
            : 0;
    }
}
