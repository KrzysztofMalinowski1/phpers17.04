<?php

declare(strict_types=1);

namespace App\Tests\functional\Dummy;

use App\Entity\Order;
use App\Entity\Product;
use App\Enum\Status;
use App\Message\ErpNotification;
use App\Tests\functional\Helpers\BaseApplicationTest;
use App\Tests\functional\Helpers\FileContentHelper;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;

class Dummy5Test extends BaseApplicationTest
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function testCreateNewOrder(): void
    {
        //given
        $params = [
            'payerName' => $this->faker->name,
            'address' => $this->faker->address,
            'productIds' => []
        ];

        //and: create test products for total of 60
        foreach (range(1, 3) as $i) {
            $entityManager = $this->entityManager();
            $product = new Product(
                $this->faker->name,
                $i * 10
            );
            $entityManager->persist($product);
            $entityManager->flush();
            $params['productIds'][] = $product->getId();
        }

        //when
        $response = $this->authorizedRequest(
            'POST',
            '/api/order',
            $params,
            FileContentHelper::json('Request', 'base_order')
        );

        usleep(500000);

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validated if response contains required fields
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('order_id', $response);
        self::assertArrayHasKey('amount', $response);
        self::assertSame(60, $response['amount']);

        //and: validated created order
        $order = $this->entityManager()->getRepository(Order::class)->find($response['order_id']);
        self::assertSame(60, $order->getAmount());
        self::assertSame($params['payerName'], $order->getPayerName());
        self::assertSame($params['address'], $order->getAddress());
        self::assertSame(Status::NEW, $order->getStatus());

        //and: validated erp notification https://github.com/zenstruck/messenger-test
        $this->transport('erp.notification')->queue()->assertContains(ErpNotification::class, 1);

        /** @var ErpNotification $message */
        $message = $this->transport('erp.notification')->queue()->messages(ErpNotification::class)[0];
        self::assertSame($response['order_id'], $message->orderId);
        self::assertSame(60, $message->amount);
        self::assertSame(Status::NEW, $message->status);

        //for in memory self::getContainer()->get('messenger.transport.erp.notification')->getSent();
    }
}
