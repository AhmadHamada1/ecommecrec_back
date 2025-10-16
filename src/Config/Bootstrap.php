<?php

namespace App\Config;

use App\Config\Doctrine;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

class Bootstrap
{
    public static function getEntityManager(): EntityManager
    {
        return Doctrine::createEntityManager();
    }
}
