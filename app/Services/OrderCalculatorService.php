<?php

namespace App\Services;

use App\DTO\OrderData;
use App\Enums\UserType;
use App\Services\Coupons\CouponManager;
use App\Services\TaxCalculatorService;
use App\Services\CashbackCalculatorService;

class OrderCalculatorService
{
    private const int CASHBACK_PERCENT = 2;

    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;
    protected float $tax = 0;
    protected float $cashback = 0;

    public function __construct(
        protected OrderData $order,
        protected CouponManager $couponManager,
        protected TaxCalculatorService $taxCalculator,
        protected CashbackCalculatorService $cashbackCalculator,
    ) {
    }

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
     * Final calculation
     */
    public function getFinalTotal(): array
    {
        $this->discount = 0;
        $this->deliveryCost = 0;

        $this->calculateBaseTotal();
        $this->applyUserDiscount();
        $this->applyCoupon();

        // discount limit — no more than 30%
        $maxDiscount = $this->baseTotal * 0.30;
        $this->discount = min($this->discount, $maxDiscount);

        $tax = $this->taxCalculator->calculate(
            $this->baseTotal,
            $this->discount
        );
        $this->calculateDeliveryCost();

        $finalTotal = max(
            $this->baseTotal - $this->discount + $tax + $this->deliveryCost,
            0
        );

        $cashback = $this->cashbackCalculator->calculate(
            $finalTotal,
            $this->order->userType
        );
        // rounding
        $finalTotal = round($finalTotal, 2);
        $this->cashback = (int) round($cashback);

        return [
            'base_total' => round($this->baseTotal, 2),
            'discount' => round($this->discount, 2),
            'tax' => round($tax, 2),
            'delivery' => $this->deliveryCost,
            'cashback' => $cashback,
            'final_total' => $finalTotal,
        ];
    }
}
