<?php

use App\Controllers\MesaController;
use App\Controllers\ReservaController;
use App\Middleware\TokenMiddleware;
use Slim\App;

return function (App $app) {
    $app->group('', function ($group) {
        // Mesas
        $group->get('/mesas', [MesaController::class, 'index']);
        $group->get('/mesas/{id}', [MesaController::class, 'show']);
        $group->post('/mesas', [MesaController::class, 'store']);
        $group->put('/mesas/{id}', [MesaController::class, 'update']);
        $group->patch('/mesas/{id}/estado', [MesaController::class, 'cambiarEstado']);

        // Reservas
        $group->get('/reservas', [ReservaController::class, 'index']);
        $group->get('/reservas/{id}', [ReservaController::class, 'show']);
        $group->post('/reservas', [ReservaController::class, 'store']);
        $group->put('/reservas/{id}', [ReservaController::class, 'update']);
        $group->patch('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);
    })->add(TokenMiddleware::class);
};