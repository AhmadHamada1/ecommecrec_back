<?php

namespace App\Repository;

use App\Entities\OrderItem;
use App\Entities\Order;
use App\Entities\Product;
use Doctrine\ORM\EntityRepository;
use GraphQL\Error\UserError;

class OrderItemRepository extends EntityRepository
{
    public function findOrFail(int $id): OrderItem
    {
        $item = $this->find($id);
        if (!$item) {
            throw new UserError("OrderItem with ID '{$id}' not found.");
        }
        return $item;
    }

    public function findOrCreate(Order $order, Product $product, int $quantity, float $price): OrderItem
{
    $item = $this->findOneBy(['order' => $order, 'product' => $product]);
    if ($item) {
        $item->quantity += $quantity;
        return $item;
    }

    $item = new OrderItem();
    $item->order = $order;
    $item->product = $product;
    $item->quantity = $quantity;
    $item->price = $price;

    $em = $this->getEntityManager();
    $em->persist($item);

    $order->addOrderItem($item);

    return $item;
}


    public function persist(OrderItem $orderItem): void
    {
        $this->getEntityManager()->persist($orderItem);
    }


    public function save(OrderItem $orderItem): void
    {
        $em = $this->getEntityManager();
        $em->persist($orderItem);
        $em->flush();
    }

    public function findByOrder(Order $order): array
    {
        return $this->findBy(['order' => $order]);
    }
    public function delete(OrderItem $orderItem): void
    {
        $em = $this->getEntityManager();
        $em->remove($orderItem);
        $em->flush();
    }
}
