<?php

namespace App\Services;

use App\DTO\OrderData;
use App\Enums\UserType;
use App\Services\Coupons\CouponManager;

class OrderCalculatorService
{
    private const int VAT_PERCENT = 20;
    private const int CASHBACK_PERCENT = 2;

    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;
    protected float $tax = 0;
    protected float $cashback = 0;

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
     * Cashback
     */
    public function calculateCashback(float $finalTotal): void
    {
        if ($this->order->userType !== UserType::VIP) {
            $this->cashback = 0;
            return;
        }

        $this->cashback = $finalTotal * (self::CASHBACK_PERCENT / 100);
    }

    /**
     * Final calculation
     */
    public function getFinalTotal(): array
    {
        $this->discount = 0;
        $this->deliveryCost = 0;
        $this->tax = 0;
        $this->cashback = 0;

        $this->calculateBaseTotal();
        $this->applyUserDiscount();
        $this->applyCoupon();

        // discount limit — no more than 30%
        $maxDiscount = $this->baseTotal * 0.30;
        $this->discount = min($this->discount, $maxDiscount);

        $this->calculateTax();
        $this->calculateDeliveryCost();

        $finalTotal = max(
            $this->baseTotal - $this->discount + $this->tax + $this->deliveryCost,
            0
        );

        $this->calculateCashback($finalTotal);
        // rounding
        $finalTotal = round($finalTotal, 2);
        $this->cashback = (int) round($this->cashback);

        return [
            'base_total' => $this->baseTotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'delivery' => $this->deliveryCost,
            'cashback' => $this->cashback,
            'final_total' => $finalTotal,
        ];
    }
}
