<?php

namespace App\Controllers;

use App\Services\AuthService;
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
}