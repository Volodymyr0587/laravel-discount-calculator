<?php

namespace App\Services\Coupons;

use App\Enums\CouponCode;

class Sale10Coupon implements CouponStrategy
{
    private const float DISCOUNT = 0.10;

    public function supports(?CouponCode $coupon): bool
    {
        return $coupon === CouponCode::SALE10;
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 1000
            ? $baseTotal * self::DISCOUNT
            : 0;
    }
}
