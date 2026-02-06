<?php

use App\DTO\OrderData;
use App\DTO\OrderItemData;
use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Enums\UserType;
use App\Services\OrderCalculatorService;

function makeOrder(
    UserType $userType = UserType::REGULAR,
    DeliveryType $deliveryType = DeliveryType::COURIER,
    ?CouponCode $coupon = null,
): OrderData {
    return new OrderData(
        items: [
            new OrderItemData(price: 1200, qty: 2),
            new OrderItemData(price: 500, qty: 1),
        ],
        userType: $userType,
        deliveryType: $deliveryType,
        coupon: $coupon,
    );
}

it('calculates order total without coupon', function () {
    $order = makeOrder(
        userType: UserType::REGULAR,
        coupon: null,
    );

    $calculator = app(OrderCalculatorService::class, [
        'order' => $order,
    ]);

    $result = $calculator->getFinalTotal();

    expect($result['base_total'])->toBe(2900.0)
        ->and($result['discount'])->toBeGreaterThan(0)
        ->and($result['tax'])->toBeGreaterThan(0)
        ->and($result['final_total'])->toBeGreaterThan(0);
});

it('applies SALE10 coupon correctly', function () {
    $order = makeOrder(
        userType: UserType::REGULAR,
        coupon: CouponCode::SALE10
    );

    $calculator = app(OrderCalculatorService::class, [
        'order' => $order,
    ]);

    $result = $calculator->getFinalTotal();

    expect($result['discount'])->toBeGreaterThan(290);
});

it('makes courier delivery free for large orders', function () {
    $order = makeOrder(
        deliveryType: DeliveryType::COURIER
    );

    $calculator = app(OrderCalculatorService::class, [
        'order' => $order,
    ]);

    $result = $calculator->getFinalTotal();

    expect($result['delivery'])->toBe(0.0);
});

it('gives cashback only to vip users', function () {
    $vipOrder = makeOrder(
        userType: UserType::VIP
    );

    $regularOrder = makeOrder(
        userType: UserType::REGULAR
    );

    $vipCalculator = app(OrderCalculatorService::class, [
        'order' => $vipOrder,
    ]);

    $regularCalculator = app(OrderCalculatorService::class, [
        'order' => $regularOrder,
    ]);

    $vipResult = $vipCalculator->getFinalTotal();
    $regularResult = $regularCalculator->getFinalTotal();

    expect($vipResult['cashback'])->toBeGreaterThan(0)
        ->and($regularResult['cashback'])->toBe(0.0);
});

it('rounds final total and cashback correctly', function () {
    $order = makeOrder(
        userType: UserType::VIP,
        coupon: CouponCode::SALE20
    );

    $calculator = app(OrderCalculatorService::class, [
        'order' => $order,
    ]);

    $result = $calculator->getFinalTotal();

    expect($result['final_total'])->toEqual(round($result['final_total'], 2))
        ->and(round($result['cashback'], 2))->toBeFloat();
});
