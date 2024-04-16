<?php

declare(strict_types=1);

namespace App\Repository\Infrastructure;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Order   find($id, $lockMode = null, $lockVersion = null)
 * @method null|Order   findOneBy(array $criteria, array $orderBy = null)
 * @method array<Order> findAll()
 * @method array<Order> findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository implements \App\Repository\OrderRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function persist($object): void
    {
        $this->getEntityManager()->persist($object);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
