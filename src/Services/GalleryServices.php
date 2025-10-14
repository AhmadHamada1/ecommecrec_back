<?php

namespace App\Services;

use App\Entities\Gallery;
use App\Entities\Product;
use App\Repository\GalleryRepository;
use GraphQL\Error\UserError;

class GalleryServices
{
    private GalleryRepository $galleryRepository;

    public function __construct(GalleryRepository $galleryRepository)
    {
        $this->galleryRepository = $galleryRepository;
    }

    public function addGalleryItem(Product $product, string $imageUrl): Gallery
    {
        $gallery = new Gallery();
        $gallery->image_url = $imageUrl;
        $gallery->product = $product;

        $this->galleryRepository->persist($gallery);
        return $gallery;
    }
}