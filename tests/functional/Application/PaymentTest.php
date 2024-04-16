<?php

declare(strict_types=1);

namespace App\Tests\functional\Application;

use App\Entity\Order;
use App\Entity\Product;
use App\Enum\Status;
use App\Tests\functional\Helpers\BaseApplicationTest;
use App\Tests\functional\Helpers\FileContentHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class PaymentTest extends BaseApplicationTest
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function testNotLoggedUserTryPayForOrder(): void
    {
        $response = $this->request('POST', '/api/payment/1');

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testOrderNotExists(): void
    {
        $response = $this->authorizedRequest('POST', '/api/payment/10000');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testSuccessPayment(): void
    {
        //given
        //create order for total of 120
        $order = $this->createOrder();

        //and: set payment client response - success
        $this->overrideGuzzleClient('app.guzzle.tpay', [
            new GuzzleResponse(200, [], FileContentHelper::json('Response', 'success_response')),
        ]);

        //when
        $response = $this->authorizedRequest('POST', "/api/payment/{$order->getId()}");

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validate if response has payment url
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('url', $response);
        self::assertSame('https://secure.sandbox.tpay.com/?title=TR-MD1235-YA5M', $response['url']);

        //and: validated order status
        $order = $this->entityManager()->getRepository(Order::class)->find($order->getId());
        self::assertSame(Status::WAITING_FOR_CONFIRMATION, $order->getStatus());
        self::assertSame("ta_q76mzGBEN8NlMB0K", $order->getPaymentId());
    }

    public function testFailedPayment(): void
    {
        //given
        //create order for total of 120
        $order = $this->createOrder();

        //and: set payment client response - failure
        $this->overrideGuzzleClient('app.guzzle.tpay', [
            new GuzzleResponse(500, [], FileContentHelper::json('Response', 'failure_response')),
        ]);

        //when
        $response = $this->authorizedRequest('POST', "/api/payment/{$order->getId()}");

        //then
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        //and: validated order status
        $order = $this->entityManager()->getRepository(Order::class)->find($order->getId());
        self::assertSame(Status::FAILURE, $order->getStatus());
        self::assertNull($order->getPaymentId());
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
            products: $products
        );

        $entityManager->persist($order);
        $entityManager->flush();

        return $order;
    }
}
