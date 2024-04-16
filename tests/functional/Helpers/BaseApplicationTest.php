<?php

declare(strict_types=1);

namespace App\Tests\functional\Helpers;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

abstract class BaseApplicationTest extends WebTestCase
{
    use InteractsWithMessenger;

    protected KernelBrowser $client;
    protected Application $application;
    protected ObjectManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bootApplication();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function bootApplication(): void
    {
        $this->client = static::getKernelClient();
        $kernel = static::$kernel;
        $this->application = new Application($kernel);
    }

    protected static function getKernelClient(): KernelBrowser
    {
        if (false === static::$booted) {
            return self::createClient();
        }

        return static::$kernel->getContainer()->get('test.client');
    }

    protected function resetClient(): void
    {
        self::ensureKernelShutdown();
        $this->bootApplication();
    }

    protected function request(string $method, string $url, array $params = [], ?string $jsonContent = null): Response
    {
        $this->client->restart();

        $this->client->jsonRequest(
            $method,
            $url,
            null !== $jsonContent ? [...json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR), ... $params] : []
        );

        return $this->client->getResponse();
    }

    protected function authorizedRequest(string $method, string $url, array $params = [], ?string $jsonContent = null, bool $forceReset = false): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([]);
        $this->client->loginUser($user);

        if($forceReset){
            $this->client->restart();
        }

        $this->client->jsonRequest(
            $method,
            $url,
            null !== $jsonContent ? [...json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR), ... $params] : []
        );

        return $this->client->getResponse();
    }

    protected function entityManager(): ObjectManager
    {
        return $this->entityManager;
    }

    protected function overrideGuzzleClient(string $serviceId, array $responses): void
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client([
            'handler' => $handlerStack,
            'http_errors' => true,
            'headers' => ['Content-Type' => 'application/json'],
        ]);
        self::getContainer()->set($serviceId, $client);
    }
}
