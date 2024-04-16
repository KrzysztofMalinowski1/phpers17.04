<?php

declare(strict_types=1);

namespace App\Tests\functional\Application;

use App\Entity\Order;
use App\Entity\Product;
use App\Enum\Status;
use App\Message\CompleteOrder;
use App\Tests\functional\Helpers\BaseApplicationTest;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Factory;
use Faker\Generator;

class CompleteOrderHandlerTest extends BaseApplicationTest
{
    private const PAYMENT_ID = 'ta_q76mzGBEN8NlMB0K';
    private Generator $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function testConsumeCompleteOrderMessage(): void
    {
        //given
        //and: create order for total of 120
        $order = $this->createOrder();

        //and: add message to transport
        $this->transport('complete.order')->send(new CompleteOrder($order->getId()));

        //when
        //(new CompleteOrderHandler(self::getContainer()->get(OrderRepository::class)))(new CompleteOrder($order->getId()));
        $this->transport('complete.order')->processOrFail(1);

        //then
        $this->transport('complete.order')->queue()->assertEmpty();

        //and: validated order status
        $order = $this->entityManager()->getRepository(Order::class)->find($order->getId());
        self::assertSame(Status::COMPLETED, $order->getStatus());
    }

    private function createOrder(): Order
    {
        $products = new ArrayCollection();

        foreach (range(1, 3) as $i) {
            $entityManager = $this->entityManager();
            $product = new Product(
                $this->faker->name,
                $i * 20
            );
            $entityManager->persist($product);
            $entityManager->flush();
            $products->add($product);
        }

        $order = new Order(
            $this->faker->name,
            $this->faker->address,
            products: $products,
            status: Status::IN_PROGRESS
        );

        $order->setPaymentId(self::PAYMENT_ID);

        $entityManager->persist($order);
        $entityManager->flush();

        return $order;
    }
}
