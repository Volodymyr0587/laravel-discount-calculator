<?php

namespace App\DTO;

class OrderItemData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly float $price,
        public readonly int $qty,
    ){}

    public function total(): float
    {
        return $this->price * $this->qty;
    }
}
