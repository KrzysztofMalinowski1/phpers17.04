<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Request\OrderRequest;
use App\Services\OrderStrategy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/order', methods: 'POST')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderStrategy $orderStrategy,
    )
    {
    }

    public function __invoke(
        #[CurrentUser] ?User $user,
        #[MapRequestPayload] OrderRequest $orderRequest,
    ): JsonResponse
    {
        $order = $this->orderStrategy->handle($user->getId(), $orderRequest);

        return new JsonResponse([
            'order_id' => $order->getId(),
            'amount' => $order->getAmount(),
        ]);
    }
}
