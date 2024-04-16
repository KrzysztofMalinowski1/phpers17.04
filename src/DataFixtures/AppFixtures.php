<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create();
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->generateProducts($manager);
        $this->generateUsers($manager);
        $this->generateOrders($manager);
    }

    private function generateProducts(ObjectManager $manager): void
    {
        foreach (range(1, 10) as $i) {
            $product = new Product(
                $this->faker->name,
                $this->faker->numberBetween(1, 100)
            );
            $manager->persist($product);
        }

        $manager->flush();
    }

    private function generateUsers(ObjectManager $manager): void
    {
        foreach (range(1, 2) as $i) {
            $user = new User($this->faker->email);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function generateOrders(ObjectManager $manager): void
    {
        foreach (range(1, 5) as $i) {
            $products = $manager->getRepository(Product::class)->findAll();
            shuffle($products);

            $order = new Order(
                $this->faker->name,
                $this->faker->address
            );

            foreach (array_slice($products, 0, $i) as $product) {
                $order->addProduct($product);
            }

            $manager->persist($order);
        }

        $manager->flush();
    }
}
