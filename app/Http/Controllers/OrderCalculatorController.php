<?php

namespace App\Http\Controllers;

use App\DTO\OrderData;
use App\Enums\UserType;
use App\Enums\CouponCode;
use App\DTO\OrderItemData;
use App\Enums\DeliveryType;
use App\Services\OrderCalculatorService;
use App\Http\Requests\OrderCalculatorRequest;

class OrderCalculatorController extends Controller
{
    /**
     * Show the order calculator form
     */
    public function create()
    {
        return view('orders.calculator', [
            'userTypes' => UserType::cases(),
            'deliveryTypes' => DeliveryType::cases(),
            'couponCodes' => CouponCode::cases(),
        ]);
    }

    /**
     * Calculate the order total
     */
    public function calculate(OrderCalculatorRequest $request)
    {
        $validated = $request->validated();

        // Build order items from validated data
        $items = collect($validated['items'])->map(function ($item) {
            return new OrderItemData(
                price: (float) $item['price'],
                qty: (int) $item['qty']
            );
        })->all();

        // Create order DTO
        $order = new OrderData(
            items: $items,
            userType: UserType::from($validated['user_type']),
            deliveryType: DeliveryType::from($validated['delivery_type']),
            coupon: isset($validated['coupon']) ? CouponCode::from($validated['coupon']) : null,
        );

        // Calculate totals
        $calculator = app(OrderCalculatorService::class, [
            'order' => $order,
        ]);

        $result = $calculator->getFinalTotal();

        // Return JSON for AJAX requests or redirect back with data
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        }

        return back()->with([
            'calculation' => $result,
            'orderData' => $validated
        ]);
    }
}
