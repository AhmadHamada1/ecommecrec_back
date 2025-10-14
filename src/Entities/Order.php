<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: \App\Repository\OrderRepository::class)]
#[ORM\Table(name: '`Order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => 0.00])]
    public float $total = 0.00;

    #[ORM\Column(type: 'datetime')]
    public \DateTime $created_at;

    #[ORM\OneToMany(mappedBy: "order", targetEntity: OrderItem::class, fetch: "EAGER", cascade: ["persist", "remove"])]
    public Collection $orderItems;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->orderItems  = new ArrayCollection();
    }

    public function addOrderItem(OrderItem $item): self
{
    if (!$this->orderItems->contains($item)) {
        $this->orderItems->add($item);
        $item->order = $this;
    }
    return $this;
}

}
