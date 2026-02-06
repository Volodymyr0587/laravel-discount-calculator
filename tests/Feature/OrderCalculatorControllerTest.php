<?php

use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Enums\UserType;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;


it('displays the calculator form', function () {
    $response = get(route('order.calculator'));

    $response->assertStatus(200)
        ->assertViewIs('orders.calculator')
        ->assertViewHas('userTypes')
        ->assertViewHas('deliveryTypes')
        ->assertViewHas('couponCodes')
        ->assertSee('Order Calculator')
        ->assertSee('User Type')
        ->assertSee('Delivery Type');
});

it('calculates order with valid data', function () {
    $data = [
        'items' => [
            ['price' => 1200, 'qty' => 2],
            ['price' => 500, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
        'coupon' => CouponCode::SALE10->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHas('calculation')
        ->assertSessionHas('orderData')
        ->assertRedirect();

    $calculation = session('calculation');
    expect($calculation)->toHaveKeys(['base_total', 'discount', 'tax', 'delivery', 'cashback', 'final_total'])
        ->and($calculation['base_total'])->toBe(2900.0);
});

it('validates required fields', function () {
    $response = post(route('order.calculate'), []);

    $response->assertSessionHasErrors(['items', 'user_type', 'delivery_type']);
});

it('validates items array is required', function () {
    $data = [
        'items' => [],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items');
});

it('validates item price is numeric and positive', function () {
    $data = [
        'items' => [
            ['price' => -100, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items.0.price');
});

it('validates item quantity is positive integer', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 0],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items.0.qty');
});

it('validates user type is valid enum value', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 1],
        ],
        'user_type' => 'invalid_type',
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('user_type');
});

it('validates delivery type is valid enum value', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => 'invalid_delivery',
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('delivery_type');
});

it('validates coupon is optional but must be valid enum if provided', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
        'coupon' => 'invalid_coupon',
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('coupon');
});

it('accepts null coupon', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasNoErrors()
        ->assertSessionHas('calculation');
});

it('returns json response for ajax requests', function () {
    $data = [
        'items' => [
            ['price' => 1200, 'qty' => 2],
            ['price' => 500, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = postJson(route('order.calculate'), $data);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'success',
            'data' => [
                'base_total',
                'discount',
                'tax',
                'delivery',
                'cashback',
                'final_total',
            ],
        ]);
});

it('calculates correctly with multiple items', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 2],
            ['price' => 200, 'qty' => 1],
            ['price' => 50, 'qty' => 3],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::PICKUP->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasNoErrors();

    $calculation = session('calculation');
    expect($calculation['base_total'])->toBe(550.0); // (100*2) + (200*1) + (50*3)
});

it('handles vip user with cashback', function () {
    $data = [
        'items' => [
            ['price' => 1000, 'qty' => 1],
        ],
        'user_type' => UserType::VIP->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $calculation = session('calculation');
    expect($calculation['cashback'])->toBeGreaterThan(0);
});

it('validates maximum number of items', function () {
    $items = [];
    for ($i = 0; $i < 51; $i++) {
        $items[] = ['price' => 100, 'qty' => 1];
    }

    $data = [
        'items' => $items,
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items');
});

it('validates item price maximum value', function () {
    $data = [
        'items' => [
            ['price' => 1000000, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items.0.price');
});

it('validates item quantity maximum value', function () {
    $data = [
        'items' => [
            ['price' => 100, 'qty' => 1001],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors('items.0.qty');
});

it('preserves old input on validation failure', function () {
    $data = [
        'items' => [
            ['price' => -100, 'qty' => 1],
        ],
        'user_type' => UserType::REGULAR->value,
        'delivery_type' => DeliveryType::COURIER->value,
    ];

    $response = post(route('order.calculate'), $data);

    $response->assertSessionHasErrors()
        ->assertSessionHasInput('user_type', UserType::REGULAR->value)
        ->assertSessionHasInput('delivery_type', DeliveryType::COURIER->value);
});
