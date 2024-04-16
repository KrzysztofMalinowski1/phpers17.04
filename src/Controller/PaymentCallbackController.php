<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Enum\Status;
use App\Message\CompleteOrder;
use App\Message\ErpNotification;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/payment/callback/{paymentId}', methods: 'POST')]
class PaymentCallbackController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MessageBusInterface $messageBus
    )
    {
    }

    public function __invoke(Order $order): JsonResponse
    {
        $order->setStatus(Status::PAID);
        $this->orderRepository->persist($order);
        $this->orderRepository->flush();

        $this->messageBus->dispatch(
            new ErpNotification(
                $order->getId(),
                $order->getAmount(),
                $order->getStatus(),
            )
        );

        $this->messageBus->dispatch(new CompleteOrder($order->getId()));

        return new JsonResponse(['success' => true]);
    }
}
