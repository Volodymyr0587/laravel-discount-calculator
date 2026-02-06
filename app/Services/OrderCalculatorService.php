<?php

namespace App\Services;

use App\Enums\UserType;
use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Services\Coupons\CouponManager;

class OrderCalculatorService
{
    private const int VAT_PERCENT = 20;

    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;
    protected float $tax = 0;

    public function __construct(
        protected array $order,
        protected CouponManager $couponManager
    ){}

    /**
     * Base amount
     */
    public function calculateBaseTotal(): float
    {
        $this->baseTotal = 0;

        foreach ($this->order['items'] as $item) {
            $this->baseTotal += $item['price'] * $item['qty'];
        }

        return $this->baseTotal;
    }

    /**
     * Discount by user type
     */
    public function applyUserDiscount(): void
    {
        $userType = UserType::tryFrom($this->order['user_type']);

        $discountPercent = $userType?->discountPercent() ?? 0;

        $this->discount += $this->baseTotal * ($discountPercent / 100);
    }

    /**
     * Coupons
     */
    public function applyCoupon(): void
    {
        $coupon = CouponCode::tryFrom($this->order['coupon']);

        $this->discount += $this->couponManager->calculateDiscount(
            $coupon,
            $this->baseTotal
        );
    }

    /**
     * Delivery
     */
    public function calculateDeliveryCost(): void
    {
        $deliveryType = DeliveryType::tryFrom($this->order['delivery_type']);

        $this->deliveryCost = $deliveryType?->cost($this->baseTotal) ?? 0;
    }

    /**
     * Tax
     */
    public function calculateTax(): void
    {
        $taxableAmount = max($this->baseTotal - $this->discount, 0);

        $this->tax = $taxableAmount * (self::VAT_PERCENT / 100);
    }

    /**
     * Final calculation
     */
    public function getFinalTotal(): array
    {
        $this->discount = 0;
        $this->deliveryCost = 0;
        $this->tax = 0;

        $this->calculateBaseTotal();
        $this->applyUserDiscount();
        $this->applyCoupon();
        $this->calculateTax();
        $this->calculateDeliveryCost();

        // discount limit — no more than 30%
        $maxDiscount = $this->baseTotal * 0.30;
        $this->discount = min($this->discount, $maxDiscount);

        $finalTotal = max(
            $this->baseTotal - $this->discount + $this->tax + $this->deliveryCost,
            0
        );

        return [
            'base_total' => $this->baseTotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'delivery' => $this->deliveryCost,
            'final_total' => $finalTotal,
        ];
    }
}
