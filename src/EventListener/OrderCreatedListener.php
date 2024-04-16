<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Event\OrderCreatedEvent;
use App\Message\ErpNotification;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: OrderCreatedEvent::class, method: 'onOrderCreated')]
class OrderCreatedListener
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function onOrderCreated(OrderCreatedEvent $createdEvent): void
    {
        $this->bus->dispatch(new ErpNotification($createdEvent->orderId, $createdEvent->amount, $createdEvent->status));
    }
}
