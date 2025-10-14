<?php
namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\GalleryRepository::class)]
#[ORM\Table(name: "Gallery")]
class Gallery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    public int $id;

    #[ORM\Column(type: "text")]
    public string $image_url;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: "gallery")]
    #[ORM\JoinColumn(name: "product_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    public ?Product $product = null;
}
