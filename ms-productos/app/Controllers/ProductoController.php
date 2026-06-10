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
}