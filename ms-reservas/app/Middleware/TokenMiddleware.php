<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Psr7\Response as SlimResponse;

class TokenMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);

        if (empty($token)) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token requerido'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $usuario = DB::table('usuarios')
            ->where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();

        if (!$usuario) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Token inválido o sesión expirada'
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $request = $request->withAttribute('usuario', $usuario);
        return $handler->handle($request);
    }
}