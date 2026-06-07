<?php

use App\Controllers\PedidoController;
use App\Middleware\TokenMiddleware;

// ── PEDIDOS ──────────────────────────────────────────────────
$app->get('/pedidos', [PedidoController::class, 'getPedidos'])
    ->add(new TokenMiddleware());

$app->get('/pedidos/{id}', [PedidoController::class, 'getPedido'])
    ->add(new TokenMiddleware());

$app->get('/pedidos/mesa/{mesa_id}', [PedidoController::class, 'getPedidosPorMesa'])
    ->add(new TokenMiddleware());

$app->post('/pedidos', [PedidoController::class, 'createPedido'])
    ->add(new TokenMiddleware());

$app->put('/pedidos/{id}/estado', [PedidoController::class, 'updateEstado'])
    ->add(new TokenMiddleware());

$app->delete('/pedidos/{id}', [PedidoController::class, 'deletePedido'])
    ->add(new TokenMiddleware());