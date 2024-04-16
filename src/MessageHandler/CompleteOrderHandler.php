<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\Status;
use App\Message\CompleteOrder;
use App\Repository\OrderRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CompleteOrderHandler
{
    public function __construct(private readonly OrderRepository $orderRepository)
    {
    }

    public function __invoke(CompleteOrder $message): void
    {
        //some logic
        $order = $this->orderRepository->find($message->orderId);
        $order->setStatus(Status::COMPLETED);
        $this->orderRepository->persist($order);
        $this->orderRepository->flush();
    }
}
