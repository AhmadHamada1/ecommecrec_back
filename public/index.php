<?php

use App\Config\Bootstrap;
use App\Config\Router;
use FastRoute\Dispatcher;

require_once __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *"); // or specify your frontend URL
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apollographql-client-name, apollographql-client-version");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$entityManager = Bootstrap::getEntityManager();
$dispatcher = Router::getDispatcher();

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 - Not Found";
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "405 - Method Not Allowed";
        break;

    case Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        $controller = new $class($entityManager);
        echo $controller->$method($entityManager, $vars);
        break;
}
