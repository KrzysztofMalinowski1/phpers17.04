<?php

declare(strict_types=1);

namespace App\Tests\functional\Application;

use App\Entity\Order;
use App\Entity\Product;
use App\Enum\Status;
use App\Message\CompleteOrder;
use App\Message\ErpNotification;
use App\Tests\functional\Helpers\BaseApplicationTest;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;

class PaymentCallbackTest extends BaseApplicationTest
{
    private const PAYMENT_ID = 'ta_q76mzGBEN8NlMB0K';
    private Generator $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function testOrderNotExists(): void
    {
        $response = $this->request('POST', '/api/payment/callback/10000');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetOrderCallback(): void
    {
        //given
        $paymentId = self::PAYMENT_ID;

        //and: create order for total of 120
        $order = $this->createOrder();

        //when
        $response = $this->request('POST', "/api/payment/callback/{$paymentId}");

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validate if response has payment url
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('success', $response);
        self::assertTrue($response['success']);

        //and: validated order status
        $order = $this->entityManager()->getRepository(Order::class)->find($order->getId());
        self::assertSame(Status::PAID, $order->getStatus());
        self::assertSame("ta_q76mzGBEN8NlMB0K", $order->getPaymentId());

        //and: validated erp notification message
        $this->transport('erp.notification')->queue()->assertContains(ErpNotification::class, 1);

        /** @var ErpNotification $message */
        $message = $this->transport('erp.notification')->queue()->messages(ErpNotification::class)[0];
        self::assertSame($order->getId(), $message->orderId);
        self::assertSame(120, $message->amount);
        self::assertSame(Status::PAID, $message->status);

        //and: validated complete order message
        $this->transport('complete.order')->queue()->assertContains(CompleteOrder::class, 1);

        /** @var CompleteOrder $completeOrder */
        $completeOrder = $this->transport('complete.order')->queue()->messages(CompleteOrder::class)[0];
        self::assertSame($order->getId(), $completeOrder->orderId);
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
