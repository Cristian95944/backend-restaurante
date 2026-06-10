<?php

namespace App\Controllers;

use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(
            Producto::all()->toJson()
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['nombre'])) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Nombre requerido'
                ])
            );

            return $response->withStatus(400);
        }

        if ($data['precio'] <= 0) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Precio inválido'
                ])
            );

            return $response->withStatus(400);
        }

        $producto = Producto::create($data);

        $response->getBody()->write(
            $producto->toJson()
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
