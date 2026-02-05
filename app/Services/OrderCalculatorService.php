<?php

namespace App\Services;

class OrderCalculatorService
{
    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;

    public function __construct(protected array $order)
    {}

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
        $userType = $this->order['user_type'];

        $discountPercent = match ($userType) {
            'regular' => 5,
            'vip' => 10,
            default => 0,
        };

        $this->discount += $this->baseTotal * ($discountPercent / 100);
    }

    /**
     * Coupons
     */
    public function applyCoupon(): void
    {
        $coupon = $this->order['coupon'];

        if (! $coupon) {
            return;
        }

        if ($coupon === 'SALE10' && $this->baseTotal >= 1000) {
            $this->discount += $this->baseTotal * 0.10;
        }

        if ($coupon === 'FIXED50' && $this->baseTotal >= 500) {
            $this->discount += 50;
        }
    }

    /**
     * Delivery
     */
    public function calculateDeliveryCost(): void
    {
        if ($this->order['delivery_type'] === 'pickup') {
            $this->deliveryCost = 0;
            return;
        }

        if ($this->order['delivery_type'] === 'courier') {
            $this->deliveryCost = $this->baseTotal >= 2000 ? 0 : 80;
        }
    }

    /**
     * Final calculation
     */
    public function getFinalTotal(): array
    {
        $this->calculateBaseTotal();
        $this->applyUserDiscount();
        $this->applyCoupon();
        $this->calculateDeliveryCost();

        // discount limit — no more than 30%
        $maxDiscount = $this->baseTotal * 0.30;
        $this->discount = min($this->discount, $maxDiscount);

        $finalTotal = max($this->baseTotal - $this->discount + $this->deliveryCost, 0);

        return [
            'base_total' => $this->baseTotal,
            'discount' => $this->discount,
            'delivery' => $this->deliveryCost,
            'final_total' => $finalTotal,
        ];
    }
}
