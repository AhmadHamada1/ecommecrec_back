<?php
namespace App\Repository;

use App\Entities\Product;
use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function findAllCategories(): array
    {
        return $this->findAll();
    }

    public function findProductById(string $id): ?Product
    {
        return $this->find($id);
    }

    public function findProductByName(string $name): ?Product
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function save(Product $product): Product
    {
        $em = $this->getEntityManager();
        $em->beginTransaction();
        try {
            $em->persist($product);
            $em->flush();
            $em->commit();
            $em->refresh($product);
            return $product;
        } catch (\Throwable $e) {
            $em->rollback();
            throw $e;
        }
    }

    public function delete(Product $product): void
    {
        $em = $this->getEntityManager();
        $em->remove($product);
        $em->flush();
    }

    public function findProductsByCategory(string $category_id): array
    {
        $products = $this->findBy(['category' => $category_id]);
        return count($products) > 0 ? $products : [];
    }
}
