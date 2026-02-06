<?php

use App\DTO\OrderData;
use App\Enums\UserType;
use App\Enums\CouponCode;
use App\DTO\OrderItemData;
use App\Enums\DeliveryType;
use Illuminate\Support\Facades\Route;
use App\Services\OrderCalculatorService;
use App\Http\Controllers\OrderCalculatorController;

Route::get('/', function () {
    return view('welcome');
});

// Order Calculator Routes
Route::get('/calculator', [OrderCalculatorController::class, 'create'])->name('order.calculator');
Route::post('/calculator', [OrderCalculatorController::class, 'calculate'])->name('order.calculate');

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
