<?php

namespace App\Controller;

use App\Entities\Order;
use App\Entities\Product;
use App\Entities\OrderItem;
use App\Services\OrderServices;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
use GraphQL\Error\DebugFlag;
use App\Utilities\GraphQLSchemas;

class OrderController
{
    private static ?ObjectType $orderType = null;

    public static function handle(EntityManagerInterface $em): string
    {
        try {
            $orderRepo = $em->getRepository(Order::class);
            $orderItemRepo = $em->getRepository(OrderItem::class);
            $productRepo = $em->getRepository(Product::class);
            $orderServices = new OrderServices($em, $orderRepo, $orderItemRepo, $productRepo);

            self::$orderType = new ObjectType([
                'name' => 'Order',
                'fields' => [
                    'id' => Type::nonNull(Type::int()),
                    'total' => Type::float(),
                    'orderItems' => [
                        'type' => Type::listOf(Type::string()),
                        'resolve' => function ($order) {
                            $items = $order->orderItems;
                            if (!$items || count($items) === 0) {
                                return [];
                            }

                            $productNames = [];
                            foreach ($items as $item) {
                                $product = $item->product;
                                if ($product) {
                                    $productNames[] = $product->name;
                                }
                            }

                            return $productNames;
                        },
                    ],
                    'created_at' => [
                    'type' => Type::string(),
                    'resolve' => fn($order) => $order->created_at->format('Y-m-d H:i:s'),
                ],

                ],
            ]);

            $orderItemInputType = new InputObjectType([
                'name' => 'OrderItemInput',
                'fields' => [
                    'product_id' => Type::nonNull(Type::string()),
                    'quantity'   => Type::nonNull(Type::int()),
                    'price'      => Type::nonNull(Type::float()),
                ],
            ]);

            // Queries
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'orders' => [
                        'type' => Type::listOf(self::$orderType),
                        'resolve' => fn() => $orderRepo->findAll(),
                    ],
                    'order' => [
                        'type' => self::$orderType,
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                        ],
                        'resolve' => fn($root, $args) => $orderRepo->find($args['id']),
                    ],
                ],
            ]);

            // Mutations
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'createOrder' => [
                        'type' => self::$orderType,
                        'args' => [
                            'items' => Type::nonNull(Type::listOf(GraphQLSchemas::orderItemsInputType())),
                        ],
                        'resolve' => function ($root, $args) use ($orderServices) {
                            return $orderServices->placeOrder($args['items']);
                        },
                    ],

                    'updateOrder' => [
                        'type' => self::$orderType,
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                            'total' => Type::nonNull(Type::float()),
                        ],
                        'resolve' => function ($root, $args) use ($em, $orderRepo) {
                            $order = $orderRepo->find($args['id']);
                            if (!$order) {
                                return null;
                            }
                            $order->total = $args['total'];
                            $em->flush();
                            return $order;
                        },
                    ],
                    'deleteOrder' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'id' => Type::nonNull(Type::int()),
                        ],
                        'resolve' => function ($root, $args) use ($em, $orderRepo) {
                            $order = $orderRepo->find($args['id']);
                            if (!$order) {
                                return false;
                            }
                            $em->remove($order);
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

            // Handle GraphQL request
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
