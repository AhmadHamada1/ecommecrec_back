<?php

namespace App\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

class Doctrine
{
    public static function createEntityManager(): EntityManager
    {
        // إعداد الميتاداتا الخاصة بالـ Entities
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../Entities'],
            isDevMode: true
        );

        // إعداد الاتصال بقاعدة البيانات داخل Docker
        $connection = DriverManager::getConnection([
            'dbname'   => 'ecommerce_db',       // اسم قاعدة البيانات
            'user'     => 'ecommerce_user',     // اسم المستخدم من docker-compose
            'password' => 'userpassword',       // الباسورد من docker-compose
            'host'     => 'db',                 // اسم السيرفيس داخل docker-compose
            'driver'   => 'pdo_mysql',
            'charset'  => 'utf8mb4',
            'port'     => 3306
        ], $config);

        // إنشاء EntityManager (اللي بيتعامل مع الـ ORM)
        return new EntityManager($connection, $config);
    }
}
