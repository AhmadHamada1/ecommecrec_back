<?php

namespace App\Repository;

use App\Entities\Currency;
use Doctrine\ORM\EntityRepository;

class CurrencyRepository extends EntityRepository
{
    public function save(Currency $currency): void
    {
        $this->_em->persist($currency);
        $this->_em->flush();
    }

    public function delete(Currency $currency): void
    {
        $this->_em->remove($currency);
        $this->_em->flush();
    }

    public function findCurrency(string $label, string $symbol): ?Currency
    {
        return $this->findOneBy(['label' => $label, 'symbol' => $symbol]);
    }

    public function findAllCurrencies(): array
    {
        return $this->findAll();
    }
}
