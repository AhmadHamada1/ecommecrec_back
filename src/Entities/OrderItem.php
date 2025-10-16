<?php

namespace App\Entities;
use App\Entities\Product;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: "OrderItem")]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    #[ORM\Column(type: "integer")]
    public int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: "orderItems")]
    #[ORM\JoinColumn(name: "order_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    public Order $order;

    #[ORM\Column(type: "integer", options: ["default" => 1])]
    public int $quantity = 1;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    public float $price;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: "orderItems")]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    public Product $product;

}
