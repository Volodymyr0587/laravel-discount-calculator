<?php

namespace App\Services\Coupons;

class Fixed50Coupon implements CouponStrategy
{
    private const int AMOUNT = 50;

    public function supports(?string $coupon): bool
    {
        return $coupon === 'FIXED50';
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 500
            ? self::AMOUNT
            : 0;
    }
}
