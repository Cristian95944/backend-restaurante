<?php

namespace App\Controllers;

use App\Models\Usuario;
use App\Helpers\TokenGenerator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private function responder(Response $response, array $datos, int $codigo = 200): Response
    {
        $response->getBody()->write(json_encode($datos, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }

    private function obtenerDatos(Request $request): array
    {
        $datos = $request->getParsedBody();

        if (!is_array($datos)) {
            $datos = json_decode($request->getBody()->getContents(), true);
        }

        return is_array($datos) ? $datos : [];
    }

    public function login(Request $request, Response $response): Response
    {
        $datos = $this->obtenerDatos($request);

        $acceso = $datos['usuario'] ?? $datos['correo'] ?? '';
        $clave = $datos['contrasena'] ?? '';

        if ($acceso === '' || $clave === '') {
            return $this->responder($response, [
                'estado' => false,
                'mensaje' => 'Debe ingresar usuario o correo y contraseña'
            ], 400);
        }

        $usuario = Usuario::where('usuario', $acceso)
            ->orWhere('correo', $acceso)
            ->first();

        if (!$usuario || $usuario->contrasena !== $clave) {
            return $this->responder($response, [
                'estado' => false,
                'mensaje' => 'Datos de acceso incorrectos'
            ], 401);
        }

        $token = TokenGenerator::crearToken();

        $usuario->token = $token;
        $usuario->sesion_activa = true;
        $usuario->save();

        return $this->responder($response, [
            'estado' => true,
            'mensaje' => 'Ingreso correcto',
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'usuario' => $usuario->usuario,
                'rol' => $usuario->rol
            ]
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');

        if (!$usuario) {
            return $this->responder($response, [
                'estado' => false,
                'mensaje' => 'No hay una sesión válida'
            ], 401);
        }

        $usuario->token = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        return $this->responder($response, [
            'estado' => true,
            'mensaje' => 'Sesión finalizada correctamente'
        ]);
    }

    public function validarToken(Request $request, Response $response): Response
    {
        $usuario = $request->getAttribute('usuario');

        return $this->responder($response, [
            'estado' => true,
            'mensaje' => 'Token válido',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'usuario' => $usuario->usuario,
                'rol' => $usuario->rol
            ]
        ]);
    }
}