<?php

namespace App\Services\Coupons;

interface CouponStrategy
{
    public function supports(?string $coupon): bool;

    public function apply(float $baseTotal): float;

}
