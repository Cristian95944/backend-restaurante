<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $service = new AuthService();

        $result = $service->login(
            $data['usuario'],
            $data['contrasena']
        );

        $response->getBody()->write(
            json_encode($result)
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response)
    {
        $token = str_replace(
            'Bearer ',
            '',
            $request->getHeaderLine('Authorization')
        );

        $usuario = Usuario::where(
            'token',
            $token
        )->first();

        if ($usuario) {

            $usuario->token = null;
            $usuario->sesion_activa = false;
            $usuario->save();
        }

        $response->getBody()->write(
            json_encode([
                'success' => true
            ])
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}