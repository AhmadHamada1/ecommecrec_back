<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CurrencyRepository::class)]
#[ORM\Table(name: "Currency")]
class Currency
{
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 10)]
    public string $label;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 5)]
    public string $symbol;

    public function __construct(string $label, string $symbol)
    {
        $this->label = $label;
        $this->symbol = $symbol;
    }
}
