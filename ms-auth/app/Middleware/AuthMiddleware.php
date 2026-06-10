<?php

namespace App\Middleware;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware
{
    public function __invoke(
        Request $request,
        $handler
    ): Response {

        $token = str_replace(
            'Bearer ',
            '',
            $request->getHeaderLine('Authorization')
        );

        $usuario = Usuario::where(
            'token',
            $token
        )
        ->where(
            'sesion_activa',
            true
        )
        ->first();

        if (!$usuario) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'No autorizado'
                ])
            );

            return $response
                ->withStatus(401)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }

        return $handler->handle($request);
    }
}