<?php

namespace App\Services;

use App\Entities\Price;
use App\Entities\Product;
use App\Entities\Currency;
use App\Repository\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;

class PriceServices
{
    private PriceRepository $priceRepository;
    private EntityManagerInterface $em;

    public function __construct(PriceRepository $priceRepository, EntityManagerInterface $em)
    {
        $this->priceRepository = $priceRepository;
        $this->em = $em;
    }

    public function addPrice(Product $product, float $amount, string $currencyLabel, string $currencySymbol): Price
    {
        $currency = $this->em->getRepository(Currency::class)->findOneBy([
            'label'  => $currencyLabel,
            'symbol' => $currencySymbol,
        ]);

        if (!$currency) {
            $currency = new Currency($currencyLabel, $currencySymbol);
            $this->em->persist($currency);
            $this->em->flush();
        }


        $price = new Price();
        $price->amount = $amount;
        $price->product = $product;
        $price->currency = $currency;

        $this->priceRepository->persist($price);

        return $price;
    }
}
