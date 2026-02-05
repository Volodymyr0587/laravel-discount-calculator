<?php

namespace App\Services;

class OrderCalculatorService
{
    private const float SALE10 = 0.1;
    private const float SALE20 = 0.2;
    private const int FIXED50 = 50;
    private const FREE_DELIVERY_FROM = 2000;
    private const COURIER_PRICE = 80;

    protected float $baseTotal = 0;
    protected float $discount = 0;
    protected float $deliveryCost = 0;

    public function __construct(protected array $order)
    {
    }

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

        if (!$coupon) {
            return;
        }

        match ($coupon) {
            'SALE10' => $this->baseTotal >= 1000
            ? $this->discount += $this->baseTotal * self::SALE10
            : null,

            'SALE20' => $this->baseTotal >= 2000
            ? $this->discount += $this->baseTotal * self::SALE20
            : null,

            'FIXED50' => $this->baseTotal >= 500
            ? $this->discount += self::FIXED50
            : null,

            default => null,
        };

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
            $this->deliveryCost = $this->baseTotal >= self::FREE_DELIVERY_FROM ? 0 : self::COURIER_PRICE;
        }
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
