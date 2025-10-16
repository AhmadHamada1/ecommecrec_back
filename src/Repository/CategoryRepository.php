<?php
namespace App\Repository;

use App\Entities\Category;
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findAllCategories(): array
    {
        return $this->findAll();
    }

    public function findCategoryByName(string $name): ?Category
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->persist($category);
        $em->flush();
    }

    public function delete(Category $category): void
    {
        $em = $this->getEntityManager();
        $em->remove($category);
        $em->flush();
    }
}
