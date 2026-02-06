<?php

namespace App\Services;

use App\DTO\OrderData;
use App\Services\Coupons\CouponManager;

class OrderCalculatorService
{
    private const int VAT_PERCENT = 20;

    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;
    protected float $tax = 0;

    public function __construct(
        protected OrderData $order,
        protected CouponManager $couponManager
    ){}

    /**
     * Base amount
     */
    public function calculateBaseTotal(): float
    {
        $this->baseTotal = 0;

        foreach ($this->order->items as $item) {
            $this->baseTotal += $item->total();
        }

        return $this->baseTotal;
    }

    /**
     * Discount by user type
     */
    public function applyUserDiscount(): void
    {
        $discountPercent = $this->order->userType->discountPercent();

        $this->discount += $this->baseTotal * ($discountPercent / 100);
    }

    /**
     * Coupons
     */
    public function applyCoupon(): void
    {
        $this->discount += $this->couponManager->calculateDiscount(
            $this->order->coupon,
            $this->baseTotal
        );
    }

    /**
     * Delivery
     */
    public function calculateDeliveryCost(): void
    {
        $this->deliveryCost = $this->order->deliveryType->cost($this->baseTotal);
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
