<?php

namespace App\Controller;

use App\Entities\Product;
use App\Repository\ProductRepository;
use App\Services\ProductServices;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
use GraphQL\Error\DebugFlag;
use GraphQL\Type\Definition\InputObjectType;
use App\Utilities\GraphQLSchemas;


class ProductController
{
    private static ?ObjectType $productType = null;

    public static function handle(EntityManagerInterface $em): string
    {
        try {
            $productRepo = $em->getRepository(Product::class);
            $productService = new ProductServices($productRepo, $em);

            $attributeItemInput = new InputObjectType([
                'name' => 'AttributeItemInput',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'value' => Type::nonNull(Type::string()),
                    'displayValue' => Type::nonNull(Type::string()),
                ],
            ]);

            $attributeSetInput = new InputObjectType([
                'name' => 'AttributeSetInput',
                'fields' => [
                    'id' => Type::nonNull(Type::string()),
                    'name' => Type::string(),
                    'type' => Type::string(),
                    'items' => Type::nonNull(Type::listOf(Type::nonNull($attributeItemInput))),
                ],
            ]);

                self::$productType = new ObjectType([
                    'name' => 'Product',
                    'fields' => [
                        'id' => Type::nonNull(Type::string()),
                        'name' => Type::nonNull(Type::string()),
                        'in_stock' => Type::nonNull(Type::boolean()),
                        'description' => Type::string(),
                        'brand' => Type::string(),
                        'category_id' => [
                            'type' => Type::string(),
                            'resolve' => fn(Product $product) => $product->category ? $product->category->id : null,
                        ],
                        'gallery' => [
                            'type' => Type::listOf(Type::string()),
                            'resolve' => fn(Product $product) =>
                                array_map(fn($g) => $g->image_url, $product->gallery->toArray()),
                        ],
                        'prices' => [
                            'type' => Type::listOf(GraphQLSchemas::priceType()),
                            'resolve' => fn(Product $product) => $product->prices->toArray(),
                        ],
                        'attributes' => [
                            'type' => Type::listOf(GraphQLSchemas::attributeSetType()),
                            'resolve' => function(Product $p) {
                                $sets = [];
                                foreach ($p->attributes as $attr) {
                                    $sets[] = [
                                        'id' => $attr->id,
                                        'name' => $attr->name,
                                        'type' => $attr->type,
                                        'items' => array_map(
                                            fn(\App\Entities\AttributeItem $i) => [
                                                'id' => $i->id,
                                                'value' => $i->value,
                                                'displayValue' => $i->displayValue,
                                            ],
                                            $attr->items->toArray()
                                        ),
                                    ];
                                }
                                return $sets;
                            },
                        ],
                    ]
                ]);

            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'products' => [
                        'type' => Type::listOf(self::$productType),
                        'resolve' => fn() => $productService->getAllProducts(),
                    ],
                        'product' => [
                            'type' => self::$productType,
                            'args' => [
                                'id' => Type::nonNull(Type::string()),
                            ],
                            'resolve' => fn($root, $args) => $productService->findOneProduct($args['id']),
                        ],
                    'productsByCategory' => [
                        'type' => Type::listOf(self::$productType),
                        'args' => [
                            'category_id' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => fn($root, $args) => $args['category_id'] === "all" ? $productService->getAllProducts() : $productService->getProductsByCategory($args['category_id'])
                    ]
                ],
            ]);

            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createProduct' => [
                        'type' => self::$productType,
                        'args' => [
                            'id' => Type::nonNull(Type::string()),
                            'name' => Type::nonNull(Type::string()),
                            'in_stock' => Type::nonNull(Type::boolean()),
                            'description' => Type::string(),
                            'brand' => Type::string(),
                            'category_id' => Type::string(),
                            'gallery' => Type::listOf(Type::string()),
                            'prices' => Type::listOf(GraphQLSchemas::priceInput()),
                            'attributes' => Type::listOf(Type::nonNull($attributeSetInput)),
                        ],
                        'resolve' => fn($root, $args) => $productService->createProduct(
                            $args['id'],
                            $args['name'],
                            $args['in_stock'],
                            $args['description'] ?? null,
                            $args['brand'] ?? null,
                            $args['category_id'] ?? null,
                            $args['gallery'] ?? [],
                            $args['prices'] ?? [],
                            $args['attributes'] ?? []
                        ),
                    ]
                ],
            ]);

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

        } catch (\Throwable $e) {
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
