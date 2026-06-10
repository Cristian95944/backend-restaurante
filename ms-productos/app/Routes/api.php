<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CategoriaController;
use App\Controllers\ProductoController;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    $categoriaController = new CategoriaController();
    $productoController = new ProductoController();
    $authMiddleware = new AuthMiddleware();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'estado' => true,
            'mensaje' => 'Microservicio de productos funcionando'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('', function (RouteCollectorProxy $group) use ($categoriaController, $productoController) {

        $group->get('/categorias', [$categoriaController, 'listar']);
        $group->post('/categorias', [$categoriaController, 'crear']);

        $group->get('/productos', [$productoController, 'listar']);
        $group->get('/productos/{id}', [$productoController, 'ver']);
        $group->post('/productos', [$productoController, 'crear']);
        $group->put('/productos/{id}', [$productoController, 'actualizar']);
        $group->delete('/productos/{id}', [$productoController, 'eliminar']);

    })->add($authMiddleware);
};