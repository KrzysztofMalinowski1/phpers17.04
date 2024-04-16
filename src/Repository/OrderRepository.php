<?php

declare(strict_types=1);

namespace App\Repository;

interface OrderRepository
{
    public function persist($object): void;

    public function flush(): void;
}
