<?php

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Inicializar base de datos
Database::initialize();

// Crear aplicación Slim
$app = AppFactory::create();

// Middlewares globales
$app->add(CorsMiddleware::class);
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Rutas
(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();