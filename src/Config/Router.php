<?php

namespace App\Config;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public static function getDispatcher()
    {
        return simpleDispatcher(function (RouteCollector $r) {
            $r->post('/category', [\App\Controller\CategoryController::class, 'handle']);
            $r->post('/product', [\App\Controller\ProductController::class, 'handle']);
            $r->post('/order', [\App\Controller\OrderController::class, 'handle']);
        });
    }
}
