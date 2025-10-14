<?php
namespace App\Services;

use App\Entities\Category;
use App\Repository\CategoryRepository;
use GraphQL\Error\UserError;

class CategoryServices
{
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function createCategory(string $id, string $name): Category
    {
        $existing = $this->categoryRepository->findCategoryByName($name);
        if ($existing) {
            throw new UserError("Category with name '{$name}' already exists.");
        }

        $category = new Category();
        $category->id = $id;
        $category->name = $name;

        $this->categoryRepository->save($category);

        return $category;
    }

    public function updateCategory(int $id, string $name): ?Category
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return null;
        }

        $existing = $this->categoryRepository->findCategoryByName($name);
        if ($existing && $existing->id !== $id) {
            throw new \RuntimeException("Another category with name '{$name}' already exists.");
        }

        $category->name = $name;
        $this->categoryRepository->save($category);

        return $category;
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return false;
        }

        $this->categoryRepository->delete($category);

        return true;
    }

    public function findCategory(string $id)
    {
        $category = $this->categoryRepository->findCategoryByName($id);
        return $category;
    }
}
