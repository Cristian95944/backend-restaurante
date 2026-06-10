<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\PedidoController;
use App\Controllers\DetallePedidoController;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    $pedidoController = new PedidoController();
    $detalleController = new DetallePedidoController();
    $authMiddleware = new AuthMiddleware();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'estado' => true,
            'mensaje' => 'Microservicio de pedidos funcionando'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('', function (RouteCollectorProxy $group) use ($pedidoController, $detalleController) {

        $group->get('/pedidos', [$pedidoController, 'listar']);

        $group->get('/pedidos/{id}', [$pedidoController, 'ver']);

        $group->post('/pedidos', [$pedidoController, 'crear']);

        $group->put('/pedidos/{id}/estado', [$pedidoController, 'cambiarEstado']);

        $group->get('/pedidos/{id}/detalles', [$detalleController, 'listar']);

        $group->post('/pedidos/{id}/detalles', [$detalleController, 'agregar']);

        $group->put('/detalles-pedidos/{id}', [$detalleController, 'actualizarCantidad']);

        $group->delete('/detalles-pedidos/{id}', [$detalleController, 'eliminar']);

    })->add($authMiddleware);
};