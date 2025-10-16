<?php

namespace App\Repository;

use App\Entities\Order;
use Doctrine\ORM\EntityRepository;
use GraphQL\Error\UserError;

class OrderRepository extends EntityRepository
{
    public function createOrder(): Order
    {
        $order = new Order();
        $order->createdAt = new \DateTime();
        $order->total = 0.00;

        $this->getEntityManager()->persist($order);
        return $order;
    }

        public function save(Order $order): Order
        {
            $em = $this->getEntityManager();
            $em->beginTransaction();
            try {
                $em->persist($order);
                $em->flush();
                $em->commit();
                $em->refresh($order);
                return $order;
            } catch (\Throwable $e) {
                $em->rollback();
                throw $e;
            }
        }

    public function updateTotal(Order $order): void
    {
        $total = 0;
        foreach ($order->orderItems as $item) {
            $total += $item->price * $item->quantity;
        }
        $order->total = $total;
        $this->getEntityManager()->flush();
    }
}
