<?php

use App\Controllers\ProductoController;
use App\Middleware\TokenMiddleware;

// ── CATEGORÍAS (requieren token) ─────────────────────────────
$app->get('/categorias', [ProductoController::class, 'getCategorias'])
    ->add(new TokenMiddleware());

$app->post('/categorias', [ProductoController::class, 'createCategoria'])
    ->add(new TokenMiddleware());

$app->put('/categorias/{id}', [ProductoController::class, 'updateCategoria'])
    ->add(new TokenMiddleware());

$app->delete('/categorias/{id}', [ProductoController::class, 'deleteCategoria'])
    ->add(new TokenMiddleware());

// ── PRODUCTOS (requieren token) ──────────────────────────────
$app->get('/productos', [ProductoController::class, 'getProductos'])
    ->add(new TokenMiddleware());

$app->get('/productos/{id}', [ProductoController::class, 'getProducto'])
    ->add(new TokenMiddleware());

$app->get('/productos/categoria/{id}', [ProductoController::class, 'getProductosPorCategoria'])
    ->add(new TokenMiddleware());

$app->post('/productos', [ProductoController::class, 'createProducto'])
    ->add(new TokenMiddleware());

$app->put('/productos/{id}', [ProductoController::class, 'updateProducto'])
    ->add(new TokenMiddleware());

$app->delete('/productos/{id}', [ProductoController::class, 'deleteProducto'])
    ->add(new TokenMiddleware());