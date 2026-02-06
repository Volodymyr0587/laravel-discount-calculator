<?php

namespace App\Services;

use App\Services\Coupons\CouponManager;

class OrderCalculatorService
{
    private const float SALE10 = 0.1;
    private const float SALE20 = 0.2;
    private const int FIXED50 = 50;
    private const int FREE_DELIVERY_FROM = 2000;
    private const int COURIER_PRICE = 80;
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
        $this->discount += $this->couponManager->calculateDiscount(
            $this->order['coupon'],
            $this->baseTotal
        );
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
