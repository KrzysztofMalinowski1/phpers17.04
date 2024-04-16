<?php

declare(strict_types=1);

namespace App\Event;

use App\Enum\Status;
use Symfony\Contracts\EventDispatcher\Event;

final class OrderCreatedEvent extends Event
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $amount,
        public readonly Status $status,
    )
    {
    }
}
