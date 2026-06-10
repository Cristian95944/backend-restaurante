<?php

use App\Controllers\PedidoController;

$app->get('/pedidos', [PedidoController::class, 'index']);

$app->get('/pedidos/{id}', [PedidoController::class, 'show']);

$app->post('/pedidos', [PedidoController::class, 'store']);

$app->delete('/pedidos/{id}', [PedidoController::class, 'destroy']);