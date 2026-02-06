<?php

use App\DTO\OrderData;
use App\DTO\OrderItemData;
use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Enums\UserType;
use App\Services\CashbackCalculatorService;
use App\Services\Coupons\CouponManager;
use App\Services\OrderCalculatorService;
use App\Services\TaxCalculatorService;
use Mockery\MockInterface;

// Helper to create the service with overrides
function createService(?OrderData $order = null, $mocks = []): OrderCalculatorService {
    $order = $order ?? new OrderData(
        items: [
            new OrderItemData(price: 1000, qty: 2),
            new OrderItemData(price: 500, qty: 1),
        ],
        userType: UserType::REGULAR,
        deliveryType: DeliveryType::COURIER,
        coupon: null,
    );

    return new OrderCalculatorService(
        $order,
        // Use spy() here. If you need specific return values (like floats),
        // you can configure them in the test, but this prevents the crash.
        $mocks['coupon'] ?? Mockery::spy(CouponManager::class),
        $mocks['tax'] ?? Mockery::spy(TaxCalculatorService::class),
        $mocks['cashback'] ?? Mockery::spy(CashbackCalculatorService::class)
    );
}

describe('calculateBaseTotal', function () {
    it('calculates base total from all items', function () {
        $service = createService();
        expect($service->calculateBaseTotal())->toBe(2500.0);
    });

    it('returns 0 for empty items', function () {
        $order = new OrderData([], UserType::REGULAR, DeliveryType::COURIER, null);
        $service = createService($order);

        expect($service->calculateBaseTotal())->toBe(0.0);
    });

    it('handles decimal prices safely', function () {
        $order = new OrderData([
            new OrderItemData(price: 19.99, qty: 3),
            new OrderItemData(price: 5.50, qty: 2),
        ], UserType::REGULAR, DeliveryType::COURIER, null);

        $service = createService($order);

        // Use Delta for floats
        expect($service->calculateBaseTotal())->toEqualWithDelta(70.97, 0.001);
    });
});

describe('applyCoupon', function () {
    it('applies coupon discount using spies', function () {
        // Arrange
        $couponMock = Mockery::mock(CouponManager::class); // Use Spy!
        $couponMock->shouldReceive('calculateDiscount')->andReturn(250.0);

        $order = new OrderData(
            [new OrderItemData(1000, 1)],
            UserType::REGULAR,
            DeliveryType::COURIER,
            CouponCode::SALE10
        );

        $service = createService($order, ['coupon' => $couponMock]);

        // Act
        $service->calculateBaseTotal();
        $service->applyCoupon();

        // Assert (Verify the call happened with correct args)
        $couponMock->shouldHaveReceived('calculateDiscount')
            ->with(CouponCode::SALE10, 1000.0)
            ->once();
    });
});

describe('getFinalTotal', function () {
    it('returns structured array keys', function () {
        $service = createService();

        // We can just rely on the default mocks returning null/0 unless strict types prevent it
        // Or configure them in the helper if needed.

        $result = $service->getFinalTotal();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['base_total', 'discount', 'tax', 'final_total']);
    });
});
