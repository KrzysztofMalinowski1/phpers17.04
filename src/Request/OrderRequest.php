<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class OrderRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $payerName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 10, max: 255)]
        public string $address,

        #[Assert\NotBlank]
        #[Assert\Count(min: 1)]
        public array  $productIds,
    )
    {
    }
}
