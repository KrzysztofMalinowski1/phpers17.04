<?php

declare(strict_types=1);

namespace App\Message;

/** @see CompleteOrderHandler */
readonly class CompleteOrder
{
    public function __construct(
        public int $orderId
    )
    {
    }
}
