<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\Status;

readonly class ErpNotification
{
    public function __construct(
        public int $orderId,
        public int $amount,
        public Status $status,
    )
    {
    }
}
