<?php

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response as SlimResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

require __DIR__ . '/../app/Config/database.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->add(function (Request $request, Handler $handler) {
    if ($request->getMethod() === 'OPTIONS') {
        $response = new SlimResponse();
    } else {
        $response = $handler->handle($request);
    }

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$rutas = require __DIR__ . '/../app/Routes/api.php';
$rutas($app);

$app->run();