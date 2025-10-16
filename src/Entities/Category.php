<?php
namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: \App\Repository\CategoryRepository::class)]
#[ORM\Table(name: "Category")]
class Category
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 100)]
    public ?string $id;

    #[ORM\Column(type: "string", length: 255)]
    public string $name;

    #[ORM\OneToMany(mappedBy: "category", targetEntity: Product::class, cascade: ["persist", "remove"])]
    public Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
}
