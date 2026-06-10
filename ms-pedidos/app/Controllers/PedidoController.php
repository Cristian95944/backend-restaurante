<?php

namespace App\Controllers;

use App\Models\Pedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(
            Pedido::all()->toJson()
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}