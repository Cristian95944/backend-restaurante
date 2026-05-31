<?php

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app) {
    $app->post('/auth/login', [AuthController::class, 'login']);

    $app->post('/auth/logout', [AuthController::class, 'logout'])
        ->add(AuthMiddleware::class);

    $app->get('/auth/verificar', [AuthController::class, 'verificar'])
        ->add(AuthMiddleware::class);
};