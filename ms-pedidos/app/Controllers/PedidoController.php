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

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function show(Request $request, Response $response, array $args)
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Pedido no encontrado'
                ])
            );

            return $response->withStatus(404);
        }

        $response->getBody()->write(
            $pedido->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $pedido = Pedido::create([
            'mesa_id' => $data['mesa_id'],
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'subtotal' => $data['subtotal'],
            'total' => $data['total'],
            'estado' => 'pendiente'
        ]);

        $response->getBody()->write(
            $pedido->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Pedido no encontrado'
                ])
            );

            return $response->withStatus(404);
        }

        $pedido->delete();

        $response->getBody()->write(
            json_encode([
                'success' => true
            ])
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }
}