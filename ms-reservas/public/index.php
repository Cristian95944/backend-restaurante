<?php

use App\Config\Database;
use App\Middleware\CorsMiddleware;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

Database::initialize();

$app = AppFactory::create();

$app->add(CorsMiddleware::class);
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

(require __DIR__ . '/../app/Routes/routes.php')($app);

$app->run();