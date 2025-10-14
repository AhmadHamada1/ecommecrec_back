<?php

namespace App\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class Doctrine
{
    public static function createEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../Entities'],
            isDevMode: true
        );

        $connection = DriverManager::getConnection([
            'dbname'   => 'ecommerce_db',
            'user'     => 'root',
            'password' => 'rootpassword',
            'host'     => 'localhost',
            'driver'   => 'pdo_mysql',
            'charset'  => 'utf8mb4',
        ], $config);

        return new EntityManager($connection, $config);
    }
}
