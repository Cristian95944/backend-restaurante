<?php

namespace App\Middleware;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authService = new AuthService();

        $usuario = $authService->validarToken(
            $request->getHeaderLine('Authorization')
        );

        if (!$usuario) {
            $response = new SlimResponse();

            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'No autorizado'
            ]));

            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }

        $request = $request
            ->withAttribute('usuario_id', $usuario->id)
            ->withAttribute('usuario_rol', $usuario->rol);

        return $handler->handle($request);
    }
}