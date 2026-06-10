<?php

use App\Controllers\PedidoController;

$app->get('/pedidos', [PedidoController::class, 'index']);