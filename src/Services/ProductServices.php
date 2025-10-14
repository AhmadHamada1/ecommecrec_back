<?php

namespace App\Services;

use App\Entities\Product;
use App\Entities\Gallery;
use App\Entities\Price;
use App\Entities\Category;
use App\Entities\Attribute;
use App\Entities\AttributeItem;
use App\Repository\ProductRepository;
use App\Services\GalleryServices;
use App\Services\PriceServices;
use App\Services\CategoryServices;
use App\Services\AttributeServices;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\UserError;
use Doctrine\Common\Collections\ArrayCollection;

class ProductServices
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;
    private GalleryServices $galleryServices;
    private PriceServices $priceServices;
    private CategoryServices $categoryServices;
    private AttributeServices $attributeServices;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $em)
    {
        $this->productRepository = $productRepository;
        $this->em = $em;
        $this->galleryServices = new GalleryServices($em->getRepository(Gallery::class));
        $this->priceServices = new PriceServices($em->getRepository(Price::class), $em);
        $this->categoryServices = new CategoryServices($em->getRepository(Category::class));
        $this->attributeServices = new AttributeServices($em->getRepository(Attribute::class), $em->getRepository(AttributeItem::class), $em);
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findAll();
    }

    public function findOneProduct(string $id): ?Product
    {
        return $this->productRepository->findProductById($id);
    }


public function createProduct(
    string $id,
    string $name,
    bool $in_stock,
    ?string $description = null,
    ?string $brand = null,
    ?string $category_Id = null,
    ?array $gallery = [],
    ?array $prices = [],
    ?array $attributes = []
): Product {
    $existing = $this->productRepository->findProductById($id);
    if ($existing) {
        throw new UserError("Product with id '{$id}' already exists.");
    }

    $product = new Product();
    $product->id = $id;
    $product->name = $name;
    $product->in_stock = $in_stock;
    $product->description = $description;
    $product->brand = $brand;

    if ($category_Id !== null) {
        $category = $this->categoryServices->findCategory($category_Id);
        if (!$category) {
            throw new UserError("Category with id '{$category_Id}' not found.");
        }
        $product->category = $category;
    }

    $product->gallery = new ArrayCollection();
    foreach ($gallery as $url) {
        $this->galleryServices->addGalleryItem($product, $url);
    }

    foreach ($prices as $p) {
        if (!isset($p['amount'], $p['currency']['label'], $p['currency']['symbol'])) {
            throw new UserError("Invalid price input. Expected { amount, currency: { label, symbol } }");
        }
        $this->priceServices->addPrice(
            $product,
            (float) $p['amount'],
            $p['currency']['label'],
            $p['currency']['symbol']
        );
    }

    foreach ($attributes ?? [] as $set) {
        if (!isset($set['id'])) {
            throw new UserError("Each attribute must have an 'id'.");
        }
        $name = $set['name'] ?? $set['id'];
        $type = $set['type'] ?? 'text';
        $items = $set['items'] ?? [];

        $attr = $this->attributeServices->upsertAttributeWithItemsNoFlush(
            $set['id'],
            $name,
            $type,
            $items
        );

        $product->addAttribute($attr);
    }

    return $this->productRepository->save($product);
}


    public function updateProduct(
        string $id,
        ?string $name = null,
        ?bool $inStock = null,
        ?string $description = null,
        ?string $brand = null,
        ?int $categoryId = null
    ): ?Product {
        $product = $this->productRepository->findProductById($id);
        if (!$product) {
            return null;
        }

        if ($name !== null) $product->name = $name;
        if ($inStock !== null) $product->in_stock = $inStock;
        if ($description !== null) $product->description = $description;
        if ($brand !== null) $product->brand = $brand;

        if ($categoryId !== null) {
            $category = $this->em->getRepository(Category::class)->find($categoryId);
            if (!$category) {
                throw new UserError("Category with id '{$categoryId}' not found.");
            }
            $product->category = $category;
        }

        $this->productRepository->save($product);
        return $product;
    }

    public function deleteProduct(string $id): bool
    {
        $product = $this->productRepository->findProductById($id);
        if (!$product) {
            return false;
        }

        $this->productRepository->delete($product);
        return true;
    }

    public function getProductsByCategory(string $category_id): array
    {
        return $this->productRepository->findProductsByCategory($category_id);
    }

}