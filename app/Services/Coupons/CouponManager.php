<?php

namespace App\Services\Coupons;

class CouponManager
{
    public function __construct(
        protected array $strategies
    ){}

    public function calculateDiscount(?string $coupon, float $baseTotal): float
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($coupon)) {
                return $strategy->apply($baseTotal);
            }
        }

        return 0;
    }
}
