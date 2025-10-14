<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\AttributeItemRepository::class)]
#[ORM\Table(name: "AttributeItem")]
class AttributeItem
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50)]
    public string $id;

    #[ORM\Column(type: "string", length: 100)]
    public string $value;

    #[ORM\Column(name: "display_value", type: "string", length: 100)]
    public string $displayValue;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Attribute::class, inversedBy: "items")]
    #[ORM\JoinColumn(name: "attribute_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    public ?Attribute $attribute = null;

}
