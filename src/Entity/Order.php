<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Status;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $userId;

    #[ORM\Column(length: 255)]
    private string $payerName;

    #[ORM\Column(length: 255)]
    private string $address;

    #[ORM\JoinTable(name: 'orders_products')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'product_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Product::class)]
    private Collection $products;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private Status $status;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentId = null;

    public function __construct(
        string $payerName,
        string $address,
        ?int $userId = null,
        ArrayCollection $products = new ArrayCollection(),
        Status $status = Status::NEW
    )
    {
        $this->payerName = $payerName;
        $this->address = $address;
        $this->userId = $userId;
        $this->products = $products;
        $this->status = $status;
    }

    public function addProduct(Product $product): self
    {
        $this->products->add($product);
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getPayerName(): string
    {
        return $this->payerName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getAmount(): int
    {
        return $this->products->reduce(fn (int $amount, Product $product) => $amount + $product->getPrice(), 0);
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setPaymentId(?string $paymentId): self
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }
}
