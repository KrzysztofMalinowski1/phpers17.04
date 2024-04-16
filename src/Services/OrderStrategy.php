<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Order;
use App\Event\OrderCreatedEvent;
use App\Factory\OrderFactory;
use App\Repository\OrderRepository;
use App\Request\OrderRequest;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrderStrategy
{
    public function __construct(
        private readonly OrderFactory $orderFactory,
        private readonly OrderRepository $orderRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function handle(int $userId, OrderRequest $orderRequest): Order
    {
        $order = $this->createOrder($userId, $orderRequest);
        $this->eventDispatcher->dispatch(new OrderCreatedEvent($order->getId(), $order->getAmount(), $order->getStatus()));

        return $order;
    }

    private function createOrder(int $userId, OrderRequest $orderRequest): Order
    {
        $order = $this->orderFactory->create($userId, $orderRequest->payerName, $orderRequest->address, $orderRequest->productIds);
        $this->orderRepository->persist($order);
        $this->orderRepository->flush();

        return $order;
    }
}
