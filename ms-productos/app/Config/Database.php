<?php

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

class Database
{
    public static function initialize(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'port'      => $_ENV['DB_PORT'],
            'database'  => $_ENV['DB_NAME'],
            'username'  => $_ENV['DB_USER'],
            'password'  => $_ENV['DB_PASS'],
            'charset'   => $_ENV['DB_CHARSET'],
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}