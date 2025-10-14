<?php

namespace App\Controller;

use App\Entities\Category;
use App\Repository\CategoryRepository;
use App\Services\CategoryServices;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
use GraphQL\Error\DebugFlag;

class CategoryController
{
    private static ?ObjectType $categoryType = null;

    public static function handle(EntityManagerInterface $em): string
    {
        try {
            $categoryRepo = $em->getRepository(Category::class);
            $categoryService = new CategoryServices($categoryRepo);

            // Define Category Type
            self::$categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'name' => Type::string(),
                ],
            ]);

            // Queries
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf(self::$categoryType),
                        'resolve' => fn() => $categoryRepo->findAllCategories(),
                    ],
                    'category' => [
                        'type' => self::$categoryType,
                        'args' => [
                            'id' => Type::nonNull(Type::string()),
                            'name' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => fn($root, $args) => $categoryRepo->find($args['id']),
                    ],
                ],
            ]);

            // Mutations
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createCategory' => [
                        'type' => self::$categoryType,
                        'args' => [
                            'id' => Type::nonNull(Type::string()),
                            'name' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => function ($root, $args) use ($categoryService) {
                            return $categoryService->createCategory($args['id'], $args['name']);
                        },
                    ],
                    'updateCategory' => [
                        'type' => self::$categoryType,
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                            'name' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => function ($root, $args) use ($em, $categoryRepo) {
                            $category = $categoryRepo->find($args['id']);
                            if (!$category) {
                                return null;
                            }
                            $category->setName($args['name']);
                            $em->flush();
                            return $category;
                        },
                    ],
                    'deleteCategory' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                        ],
                        'resolve' => function ($root, $args) use ($em, $categoryRepo) {
                            $category = $categoryRepo->find($args['id']);
                            if (!$category) {
                                return false;
                            }
                            $em->remove($category);
                            $em->flush();
                            return true;
                        },
                    ],
                ],
            ]);

            // Build schema
            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery($queryType)
                    ->setMutation($mutationType)
            );

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'] ?? '';
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray(DebugFlag::INCLUDE_DEBUG_MESSAGE);
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
