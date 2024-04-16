<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Order;
use App\Enum\Status;
use App\Exception\PaymentFailedException;
use App\Repository\OrderRepository;

class PaymentStrategy
{
    public function __construct(
        private readonly PaymentClient $client,
        private readonly OrderRepository $orderRepository
    )
    {
    }

    /**
     * @throws PaymentFailedException
     */
    public function handle(Order $order): string
    {
        $order = $this->updateOrder($order->setStatus(Status::IN_PROGRESS));

        try {
            $response = $this->client->transaction($order->getAmount(), $order->getPayerName());
        } catch (PaymentFailedException $e) {
            $this->updateOrder($order->setStatus(Status::FAILURE));
            throw $e;
        }

        $this->updateOrder(
            $order->setStatus(Status::WAITING_FOR_CONFIRMATION)->setPaymentId($response->transactionId)
        );

        return $response->transactionPaymentUrl;
    }

    private function updateOrder(Order $order): Order
    {
        $this->orderRepository->persist($order);
        $this->orderRepository->flush();
        return $order;
    }
}
