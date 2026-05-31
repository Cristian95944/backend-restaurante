<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    /** POST /auth/login */
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $identificador = $body['usuario'] ?? $body['correo'] ?? null;
        $contrasena    = $body['contrasena'] ?? null;

        if (!$identificador || !$contrasena) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Usuario/correo y contraseña son requeridos'
            ], 400);
        }

        $usuario = Usuario::where(function ($query) use ($identificador) {
            $query->where('usuario', $identificador)
                  ->orWhere('correo', $identificador);
        })->where('estado', 'activo')->first();

        if (!$usuario || $usuario->contrasena !== $contrasena) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = bin2hex(random_bytes(32));
        $usuario->token         = $token;
        $usuario->sesion_activa = true;
        $usuario->save();

        return $this->json($response, [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data'    => [
                'token'   => $token,
                'usuario' => [
                    'id'     => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'correo' => $usuario->correo,
                    'rol'    => $usuario->rol,
                ],
            ]
        ]);
    }

    /** POST /auth/logout */
    public function logout(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');
        $usuario->token         = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        return $this->json($response, [
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /** GET /auth/verificar */
    public function verificar(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');

        return $this->json($response, [
            'success' => true,
            'message' => 'Sesión activa',
            'data'    => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol'    => $usuario->rol,
            ]
        ]);
    }
}