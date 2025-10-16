<?php

namespace App\Repository;

use App\Entities\Gallery;
use Doctrine\ORM\EntityRepository;

class GalleryRepository extends EntityRepository
{
    public function persist(Gallery $gallery): void
    {
        $em = $this->getEntityManager();
        $em->persist($gallery);
    }

    public function saveMany(array $galleries): void {
        $em = $this->getEntityManager();
        foreach($galleries as $gallery) {
            if(!$gallery instanceof Gallery) {
                throw new \InvalidArgumentException("Expected instance of Gallery");
            }
            $em->persist($gallery);
        }
        $em->flush();
    }

    public function delete(Gallery $gallery): void
    {
        $em = $this->getEntityManager();
        $em->remove($gallery);
        $em->flush();
    }
}