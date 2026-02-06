<?php

namespace App\Services\Coupons;

use App\Enums\CouponCode;

class Fixed50Coupon implements CouponStrategy
{
    private const int AMOUNT = 50;

    public function supports(?CouponCode $coupon): bool
    {
        return $coupon === CouponCode::FIXED50;
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 500
            ? self::AMOUNT
            : 0;
    }
}
