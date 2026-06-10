<?php

use Slim\App;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {

    $auth = new AuthController();
    $middleware = new AuthMiddleware();

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'estado' => true,
            'mensaje' => 'Microservicio de autenticación funcionando'
        ], JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/login', [$auth, 'login']);

    $app->post('/logout', [$auth, 'logout'])
        ->add($middleware);

    $app->get('/validar-token', [$auth, 'validarToken'])
        ->add($middleware);
};