<?php
namespace App\Repository;

use App\Entities\Attribute;
use App\Entities\AttributeItem;
use Doctrine\ORM\EntityRepository;

class AttributeItemRepository extends EntityRepository
{

    public function findByAttribute(Attribute $attribute): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.attribute = :attr')
            ->setParameter('attr', $attribute)
            ->getQuery()
            ->getResult();
    }

    public function indexById(array $items): array
    {
        $map = [];
        foreach ($items as $item) {
            $map[$item->id] = $item;
        }
        return $map;
    }

    public function persist(AttributeItem $item): void
    {
        $this->getEntityManager()->persist($item);
    }

    public function save(AttributeItem $item): void
    {
        $em = $this->getEntityManager();
        $em->persist($item);
        $em->flush();
    }
}
