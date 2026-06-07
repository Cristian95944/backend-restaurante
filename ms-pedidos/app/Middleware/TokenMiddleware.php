<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class TokenMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Token requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = trim(str_replace('Bearer ', '', $authHeader));

        if (empty($token)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Token inválido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Verificar token contra ms-autenticacion
        $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=db_ms_autenticacion;charset=utf8mb4';
        try {
            $pdo = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE token = ? AND sesion_activa = 1");
            $stmt->execute([$token]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Token inválido o sesión cerrada']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } catch (\Exception $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Error de autenticación']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        return $handler->handle($request);
    }
}