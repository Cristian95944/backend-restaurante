<?php

use App\Controllers\MesaController;
use App\Controllers\ReservaController;

$app->get('/mesas', [MesaController::class, 'index']);

$app->get('/reservas', [ReservaController::class, 'index']);