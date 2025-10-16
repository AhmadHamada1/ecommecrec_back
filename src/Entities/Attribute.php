<?php

namespace App\Entities;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: \App\Repository\AttributeRepository::class)]
#[ORM\Table(name: "Attribute")]
class Attribute
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    public string $id;

    #[ORM\Column(type: "string", length: 100)]
    public string $name;

    #[ORM\Column(type: "string", length: 50)]
    public string $type;

    #[ORM\OneToMany(mappedBy: "attribute", targetEntity: AttributeItem::class, cascade: ["persist", "remove"])]
    public Collection $items;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: "attributes")]
    public Collection $products;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->products = new ArrayCollection();
    }
}