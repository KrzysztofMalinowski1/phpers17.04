<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class OrderFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(int $userId, string $payerName, string $address, array $productsIds): Order
    {
        $order = new Order($payerName, $address, $userId);

        foreach ($productsIds as $productId) {
            $order->addProduct($this->entityManager->getReference(Product::class, $productId));
        }

        return $order;
    }
}
