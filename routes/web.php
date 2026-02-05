<?php

use App\Services\OrderCalculatorService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-order', function () {
    $order = [
        'items' => [
            ['price' => 1200, 'qty' => 2],
            ['price' => 500, 'qty' => 1],
        ],
        'user_type' => 'regular',
        'coupon' => 'SALE10',
        'delivery_type' => 'courier',
    ];

    return (new OrderCalculatorService($order))->getFinalTotal();
});
