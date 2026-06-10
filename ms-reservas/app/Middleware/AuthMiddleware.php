<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');

        if ($authorization === '') {
            return $this->json([
                'estado' => false,
                'mensaje' => 'Token no enviado'
            ], 401);
        }

        $token = $authorization;

        if (stripos($authorization, 'Bearer ') === 0) {
            $token = substr($authorization, 7);
        }

        $token = trim($token);

        if ($token === '') {
            return $this->json([
                'estado' => false,
                'mensaje' => 'Token vacío'
            ], 401);
        }

        $request = $request->withAttribute('token', $token);

        return $handler->handle($request);
    }

    private function json(array $datos, int $codigo): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode($datos, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }
}