<?php

use App\Middleware\CorsMiddleware;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// ── Variables de entorno ─────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// ── Eloquent ORM ─────────────────────────────────────────────
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_NAME'],
    'username'  => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// ── Slim App ─────────────────────────────────────────────────
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);
$app->add(new CorsMiddleware());

// ── Rutas ────────────────────────────────────────────────────
require __DIR__ . '/../app/Routes/routes.php';

$app->run();