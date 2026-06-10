<?php

namespace App\Controllers;

use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        $resultado = $this->authService->login(
            $data['usuario'] ?? '',
            $data['contrasena'] ?? ''
        );

        return $this->json(
            $response,
            $resultado['data'],
            $resultado['status']
        );
    }

    public function logout(Request $request, Response $response): Response
    {
        $resultado = $this->authService->logout(
            $request->getHeaderLine('Authorization')
        );

        return $this->json(
            $response,
            $resultado['data'],
            $resultado['status']
        );
    }

    public function validar(Request $request, Response $response): Response
    {
        $usuario = $this->authService->validarToken(
            $request->getHeaderLine('Authorization')
        );

        if (!$usuario) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
        }

        return $this->json($response, [
            'success' => true,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'rol' => $usuario->rol
            ]
        ]);
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}