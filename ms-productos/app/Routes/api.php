<?php

use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $group) {
    $group->get('/productos', [ProductoController::class, 'listarProductos']);

    $group->get('/productos/categoria/{categoria}', [
        ProductoController::class,
        'productosPorCategoria'
    ]);

    $group->get('/productos/{id}', [ProductoController::class, 'verProducto']);

    $group->post('/productos', [ProductoController::class, 'crearProducto']);

    $group->put('/productos/{id}', [ProductoController::class, 'actualizarProducto']);

    $group->delete('/productos/{id}', [ProductoController::class, 'eliminarProducto']);

    $group->get('/categorias', [ProductoController::class, 'listarCategorias']);

    $group->post('/categorias', [ProductoController::class, 'crearCategoria']);
})->add(new AuthMiddleware());