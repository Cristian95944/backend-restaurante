<?php

use App\Controllers\MesaController;
use App\Controllers\ReservaController;

$app->get('/mesas', [MesaController::class, 'listarMesas']);

$app->get('/mesas/{id}', [MesaController::class, 'verMesa']);

$app->post('/mesas', [MesaController::class, 'crearMesa']);

$app->put('/mesas/{id}', [MesaController::class, 'actualizarMesa']);

$app->delete('/mesas/{id}', [MesaController::class, 'eliminarMesa']);

$app->get('/reservas', [ReservaController::class, 'listarReservas']);

$app->get('/reservas/{id}', [ReservaController::class, 'verReserva']);

$app->get('/reservas/mesa/{mesa}', [ReservaController::class, 'reservasPorMesa']);

$app->post('/reservas', [ReservaController::class, 'crearReserva']);

$app->put('/reservas/{id}', [ReservaController::class, 'actualizarReserva']);

$app->delete('/reservas/{id}', [ReservaController::class, 'eliminarReserva']);