<?php

use App\DTO\OrderData;
use App\DTO\OrderItemData;
use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Enums\UserType;
use App\Services\OrderCalculatorService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-order', function () {
    $order = new OrderData(
        items: [
            new OrderItemData(price: 1200, qty: 2),
            new OrderItemData(price: 500, qty: 1),
        ],
        userType: UserType::REGULAR,
        deliveryType: DeliveryType::COURIER,
        coupon: CouponCode::SALE10,
    );

    $calculator = app(OrderCalculatorService::class, [
        'order' => $order,
    ]);

    return $calculator->getFinalTotal();
});
