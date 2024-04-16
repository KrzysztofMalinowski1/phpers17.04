<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Services\PaymentStrategy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/payment/{id}', methods: 'POST')]
class PaymentController extends AbstractController
{
    public function __construct(private readonly PaymentStrategy $paymentStrategy)
    {
    }

    public function __invoke(Order $order): JsonResponse
    {
        return new JsonResponse([
            'url' => $this->paymentStrategy->handle($order)
        ]);
    }
}
