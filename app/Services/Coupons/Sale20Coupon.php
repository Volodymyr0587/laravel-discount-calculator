<?php

namespace App\Services\Coupons;

use App\Enums\CouponCode;

class Sale20Coupon implements CouponStrategy
{
    private const float DISCOUNT = 0.20;

    public function supports(?CouponCode $coupon): bool
    {
        return $coupon === CouponCode::SALE20;
    }

    public function apply(float $baseTotal): float
    {
        return $baseTotal >= 2000
            ? $baseTotal * self::DISCOUNT
            : 0;
    }
}
