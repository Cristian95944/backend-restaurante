<?php

use App\Controllers\ProductoController;
use App\Controllers\CategoriaController;

$app->get('/productos', [ProductoController::class, 'index']);

$app->get('/productos/{id}', [ProductoController::class, 'show']);

$app->post('/productos', [ProductoController::class, 'store']);

$app->put('/productos/{id}', [ProductoController::class, 'update']);

$app->delete('/productos/{id}', [ProductoController::class, 'destroy']);

$app->get(
    '/productos/categoria/{categoria}',
    [ProductoController::class, 'porCategoria']
);

$app->get('/categorias', [CategoriaController::class, 'index']);

$app->post('/categorias', [CategoriaController::class, 'store']);