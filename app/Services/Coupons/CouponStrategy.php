<?php

namespace App\Services\Coupons;

use App\Enums\CouponCode;

interface CouponStrategy
{
    public function supports(?CouponCode $coupon): bool;

    public function apply(float $baseTotal): float;

}
