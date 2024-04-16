<?php

declare(strict_types=1);

namespace App\Tests\functional\Application;

use App\Entity\Order;
use App\Entity\Product;
use App\Enum\Status;
use App\Tests\functional\Helpers\BaseApplicationTest;
use App\Tests\functional\Helpers\DatabaseTest;
use App\Tests\functional\Helpers\FileContentHelper;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\Response;

//DatabaseTest
class EntirePaymentProcessTest extends BaseApplicationTest
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function testCreateAndPaidForOrder(): void
    {
        //create new order from 5 products for total 150
        $orderId = $this->createOrder();

        //create payment url to which the payer will be redirected
        $order = $this->createPaymentUrl($orderId);

        //wait for callback form tpay
        $this->callbackFromTpay($order);

        //process rabbit message complete.order
        $this->consumeCompleteOrderMessage($order);
    }

    private function createOrder(): int
    {
        //given
        $params = [
            'payerName' => $this->faker->name,
            'address' => $this->faker->address,
            'productIds' => []
        ];

        //and: create test products for total of 60
        foreach (range(1, 5) as $i) {
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

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validated if response contains order id
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('order_id', $response);

        return $response['order_id'];
    }

    private function createPaymentUrl(int $orderId): Order
    {
        //given
        $this->resetClient();

        //and: set payment client response - success
        $this->overrideGuzzleClient('app.guzzle.tpay', [
            new GuzzleResponse(200, [], FileContentHelper::json('Response', 'success_response')),
        ]);

        //when
        $response = $this->authorizedRequest('POST', "/api/payment/{$orderId}", forceReset: true);

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validate if response has payment url
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('url', $response);
        self::assertSame('https://secure.sandbox.tpay.com/?title=TR-MD1235-YA5M', $response['url']);

        //and: validated order status
        $order = $this->entityManager()->getRepository(Order::class)->find($orderId);
        self::assertSame(Status::WAITING_FOR_CONFIRMATION, $order->getStatus());
        self::assertSame("ta_q76mzGBEN8NlMB0K", $order->getPaymentId());

        return $order;
    }

    private function callbackFromTpay(Order $order): void
    {
        //given

        //when
        $response = $this->request('POST', "/api/payment/callback/{$order->getPaymentId()}");

        //then
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        //and: validate if response has payment url
        $response = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('success', $response);
        self::assertTrue($response['success']);

        //and: validated order status
        $this->entityManager()->refresh($order);
        self::assertSame(Status::PAID, $order->getStatus());
        self::assertSame("ta_q76mzGBEN8NlMB0K", $order->getPaymentId());
    }

    private function consumeCompleteOrderMessage(Order $order): void
    {
        //given

        //when
        $this->transport('complete.order')->processOrFail(1);

        //then
        $this->transport('complete.order')->queue()->assertEmpty();

        //and: validated order status
        $this->entityManager()->refresh($order);
        self::assertSame(Status::COMPLETED, $order->getStatus());
    }
}
