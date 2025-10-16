<?php
namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\PriceRepository::class)]
#[ORM\Table(name: "Price")]
class Price
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    public int $id;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    public float $amount;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    public ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Currency::class)]
    #[ORM\JoinColumn(name: "currency_label", referencedColumnName: "label")]
    #[ORM\JoinColumn(name: "currency_symbol", referencedColumnName: "symbol")]
    public Currency $currency;

}
