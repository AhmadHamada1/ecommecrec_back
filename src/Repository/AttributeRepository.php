<?php

namespace App\Repository;

use App\Entities\Attribute;
use Doctrine\ORM\EntityRepository;
use GraphQL\Error\UserError;

class AttributeRepository extends EntityRepository
{
    public function findOrFail(string $id): Attribute
    {
        $attr = $this->find($id);
        if (!$attr) {
            throw new UserError("Attribute '{$id}' not found.");
        }
        return $attr;
    }

    public function findOrCreate(string $id, string $name, string $type): Attribute
    {
        $attr = $this->find($id);
        if ($attr) {
            return $attr;
        }

        $attr = new Attribute();
        $attr->id = $id;
        $attr->name = $name;
        $attr->type = $type;

        $this->getEntityManager()->persist($attr);
        return $attr;
    }

    public function persist(Attribute $attribute): void
    {
        $this->getEntityManager()->persist($attribute);
    }

    public function save(Attribute $attribute): void
    {
        $em = $this->getEntityManager();
        $em->persist($attribute);
        $em->flush();
    }
}
