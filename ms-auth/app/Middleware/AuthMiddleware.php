<?php

namespace App\Middleware;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthMiddleware
{
    public function __invoke(Request $request, $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');

        if (!$header) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Token requerido'
                ])
            );

            return $response->withStatus(401);
        }

        $token = str_replace(
            'Bearer ',
            '',
            $header
        );

        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', 1)
            ->first();

        if (!$usuario) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write(
                json_encode([
                    'success' => false,
                    'message' => 'Token inválido'
                ])
            );

            return $response->withStatus(401);
        }

        return $handler->handle($request);
    }
}