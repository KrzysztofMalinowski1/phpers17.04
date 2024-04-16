<?php

declare(strict_types=1);

namespace App\Services\Dto;

readonly class PaymentResponse
{
    public function __construct(
        public string $transactionPaymentUrl,
        public string $transactionId,
    )
    {
    }
}
