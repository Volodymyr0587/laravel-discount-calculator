<?php

namespace App\DTO;

use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use App\Enums\UserType;

class OrderData
{
    /**
     * @param OrderItemData[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly UserType $userType,
        public readonly DeliveryType $deliveryType,
        public readonly ?CouponCode $coupon,
    )
    {}
}
