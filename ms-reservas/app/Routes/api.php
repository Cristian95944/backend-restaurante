<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\MesaController;
use App\Controllers\ReservaController;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    $mesaController = new MesaController();
    $reservaController = new ReservaController();
    $authMiddleware = new AuthMiddleware();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'estado' => true,
            'mensaje' => 'Microservicio de reservas funcionando'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->group('', function (RouteCollectorProxy $group) use ($mesaController, $reservaController) {

        $group->get('/mesas', [$mesaController, 'listar']);
        $group->get('/mesas/{id}', [$mesaController, 'ver']);
        $group->post('/mesas', [$mesaController, 'crear']);
        $group->put('/mesas/{id}', [$mesaController, 'actualizar']);
        $group->put('/mesas/{id}/estado', [$mesaController, 'cambiarEstado']);

        $group->get('/reservas', [$reservaController, 'listar']);
        $group->get('/reservas/{id}', [$reservaController, 'ver']);
        $group->post('/reservas', [$reservaController, 'crear']);
        $group->put('/reservas/{id}', [$reservaController, 'actualizar']);
        $group->put('/reservas/{id}/estado', [$reservaController, 'cambiarEstado']);
        $group->put('/reservas/{id}/cancelar', [$reservaController, 'cancelar']);

    })->add($authMiddleware);
};