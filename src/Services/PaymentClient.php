<?php

declare(strict_types=1);

namespace App\Services;

use App\Exception\PaymentFailedException;
use App\Services\Dto\PaymentResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Serializer\SerializerInterface;

class PaymentClient
{
    public function __construct(private readonly Client $client, private readonly SerializerInterface $serializer)
    {
    }

    public function transaction(int $amount, string $payerName): PaymentResponse
    {
        try {
            $response = $this->client->post('/transactions', [
                'body' => json_encode(['amount' => $amount, 'payer_name' => $payerName], JSON_THROW_ON_ERROR),
            ]);
        } catch (GuzzleException $e) {
            throw new PaymentFailedException();
        }

        return $this->serializer->deserialize($response->getBody()->getContents(), PaymentResponse::class, 'json');
    }
}
