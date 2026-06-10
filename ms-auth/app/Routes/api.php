<?php

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;

$app->post('/login', [AuthController::class, 'login']);

$app->post('/logout', [AuthController::class, 'logout'])
    ->add(new AuthMiddleware());

$app->get('/validar-token', [AuthController::class, 'validar'])
    ->add(new AuthMiddleware());