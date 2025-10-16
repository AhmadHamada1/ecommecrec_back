<?php

use App\Config\Bootstrap;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/vendor/autoload.php';

// استدعاء الـ EntityManager من الكلاس بتاعك
$entityManager = \App\Config\Bootstrap::getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
