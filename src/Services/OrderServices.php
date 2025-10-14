<?php

namespace App\Services;

use App\Entities\Order;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use App\Repository\OrderItemRepository;
use App\Repository\ProductRepository;
use GraphQL\Error\UserError;

class OrderServices
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepo,
        private OrderItemRepository $orderItemRepo,
        private ProductRepository $productRepo
    ) {}

    public function placeOrder(array $cartItems): Order
    {
            $order = $this->orderRepo->createOrder();

            foreach ($cartItems as $cartItem) {
                $product = $this->productRepo->find($cartItem['product_id']);
                if (!$product) {
                    throw new UserError("Product {$cartItem['product_id']} not found.");
                }

                $this->orderItemRepo->findOrCreate(
                    $order,
                    $product,
                    $cartItem['quantity'],
                    $cartItem['price']
                );
            }

            $this->orderRepo->updateTotal($order);
            return $this->orderRepo->save($order);
    }
}
