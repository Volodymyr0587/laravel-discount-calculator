<?php

namespace App\Enums;

enum UserType: string
{
    case GUEST = 'guest';
    case REGULAR = 'regular';
    case VIP = 'vip';

    public function discountPercent(): int
    {
        return match ($this) {
            self::REGULAR => 5,
            self::VIP => 10,
            default => 0,
        };
    }
}
