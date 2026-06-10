<?php

namespace App\Middleware;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $token = $this->leerToken($request);

        if ($token === '') {
            return $this->respuestaJson([
                'estado' => false,
                'mensaje' => 'Token no enviado'
            ], 401);
        }

        $usuario = Usuario::where('token', $token)
            ->where('sesion_activa', true)
            ->first();

        if (!$usuario) {
            return $this->respuestaJson([
                'estado' => false,
                'mensaje' => 'Sesión no válida'
            ], 401);
        }

        $request = $request->withAttribute('usuario', $usuario);

        return $handler->handle($request);
    }

    private function leerToken(Request $request): string
    {
        $authorization = $request->getHeaderLine('Authorization');

        if (stripos($authorization, 'Bearer ') === 0) {
            $authorization = substr($authorization, 7);
        }

        return trim($authorization);
    }

    private function respuestaJson(array $datos, int $codigo): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode($datos, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }
}