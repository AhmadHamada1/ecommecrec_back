<?php

namespace App\Repository;

use App\Entities\Price;
use Doctrine\ORM\EntityRepository;

class PriceRepository extends EntityRepository
{

    public function persist(Price $price): void
    {
        $em = $this->getEntityManager();
        $em->persist($price);
    }

    // public function save(Price $price): void
    // {
    //     $em = $this->getEntityManager();
    //     $em->persist($price);
    //     $em->flush();
    // }

    public function delete(Price $price): void
    {
        $em = $this->getEntityManager();
        $em->remove($price);
        $em->flush();
    }

    public function findPriceById(int $id): ?Price
    {
        return $this->find($id);
    }

    public function findPricesByProduct(string $productId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.product', 'prod')
            ->where('prod.id = :pid')
            ->setParameter('pid', $productId)
            ->getQuery()
            ->getResult();
    }
}
