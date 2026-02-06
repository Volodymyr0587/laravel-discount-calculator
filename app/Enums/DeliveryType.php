<?php

namespace App\Enums;

enum DeliveryType: string
{
    case PICKUP = 'pickup';
    case COURIER = 'courier';

    public function cost(float $baseTotal): int
    {
        return match ($this) {
            self::PICKUP => 0,
            self::COURIER => $baseTotal >= 2000 ? 0 : 80,
        };
    }
}
