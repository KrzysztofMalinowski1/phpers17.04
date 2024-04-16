<?php

declare(strict_types=1);

namespace App\Message;

readonly class CompleteOrder
{
    public function __construct(
        public int $orderId
    )
    {
    }
}
