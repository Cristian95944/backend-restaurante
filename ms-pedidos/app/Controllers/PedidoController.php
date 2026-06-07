<?php

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    public function getPedidos(Request $request, Response $response): Response
    {
        $pedidos = Pedido::with('detalles')->get();
        $response->getBody()->write(json_encode($pedidos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getPedido(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::with('detalles')->find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($pedido));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getPedidosPorMesa(Request $request, Response $response, array $args): Response
    {
        $pedidos = Pedido::with('detalles')
            ->where('mesa_id', $args['mesa_id'])
            ->get();

        $response->getBody()->write(json_encode($pedidos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function createPedido(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['mesa_id']) || empty($data['detalles'])) {
            $response->getBody()->write(json_encode(['error' => 'mesa_id y detalles son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $subtotal = 0;
        foreach ($data['detalles'] as $detalle) {
            $subtotal += $detalle['precio_unitario'] * $detalle['cantidad'];
        }

        $pedido = Pedido::create([
            'mesa_id'  => $data['mesa_id'],
            'fecha'    => date('Y-m-d'),
            'hora'     => date('H:i:s'),
            'subtotal' => $subtotal,
            'total'    => $subtotal,
            'estado'   => 'pendiente',
        ]);

        foreach ($data['detalles'] as $detalle) {
            DetallePedido::create([
                'pedido_id'       => $pedido->id,
                'producto_id'     => $detalle['producto_id'],
                'nombre_producto' => $detalle['nombre_producto'],
                'cantidad'        => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'subtotal'        => $detalle['precio_unitario'] * $detalle['cantidad'],
            ]);
        }

        $response->getBody()->write(json_encode($pedido->load('detalles')));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function updateEstado(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();

        if (empty($data['estado'])) {
            $response->getBody()->write(json_encode(['error' => 'El estado es requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pedido->estado = $data['estado'];
        $pedido->save();

        $response->getBody()->write(json_encode($pedido->load('detalles')));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deletePedido(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            $response->getBody()->write(json_encode(['error' => 'Pedido no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $pedido->detalles()->delete();
        $pedido->delete();

        $response->getBody()->write(json_encode(['mensaje' => 'Pedido eliminado correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}