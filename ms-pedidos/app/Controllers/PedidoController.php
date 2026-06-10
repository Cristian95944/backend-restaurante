<?php

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    public function listar(Request $request, Response $response): Response
    {
        $pedidos = Pedido::with('detalles')->get();

        return $this->json($response, [
            'success' => true,
            'pedidos' => $pedidos
        ]);
    }

    public function ver(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::with('detalles')->find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'pedido' => $pedido
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        if (empty($data['mesa_id'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La mesa es obligatoria'
            ], 400);
        }

        $detalles = $data['detalles'] ?? [];

        $subtotal = $data['subtotal'] ?? $this->calcularSubtotal($detalles);
        $total = $data['total'] ?? $subtotal;

        $pedido = Pedido::create([
            'mesa_id' => $data['mesa_id'],
            'fecha' => $data['fecha'] ?? date('Y-m-d'),
            'hora' => $data['hora'] ?? date('H:i:s'),
            'subtotal' => $subtotal,
            'total' => $total,
            'estado' => $data['estado'] ?? 'pendiente'
        ]);

        foreach ($detalles as $detalle) {
            if (
                empty($detalle['nombre_producto']) ||
                empty($detalle['cantidad']) ||
                empty($detalle['precio_unitario'])
            ) {
                continue;
            }

            DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $detalle['producto_id'] ?? null,
                'nombre_producto' => $detalle['nombre_producto'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'subtotal' => $detalle['subtotal'] ?? (
                    (int) $detalle['cantidad'] * (float) $detalle['precio_unitario']
                )
            ]);
        }

        return $this->json($response, [
            'success' => true,
            'message' => 'Pedido creado correctamente',
            'pedido' => Pedido::with('detalles')->find($pedido->id)
        ], 201);
    }

    public function actualizarEstado(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        $data = $request->getParsedBody() ?? [];

        if (empty($data['estado'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El estado es obligatorio'
            ], 400);
        }

        $pedido->estado = $data['estado'];
        $pedido->save();

        return $this->json($response, [
            'success' => true,
            'message' => 'Estado del pedido actualizado correctamente',
            'pedido' => $pedido
        ]);
    }

    public function eliminar(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        DetallePedido::where('pedido_id', $pedido->id)->delete();

        $pedido->delete();

        return $this->json($response, [
            'success' => true,
            'message' => 'Pedido eliminado correctamente'
        ]);
    }

    private function calcularSubtotal(array $detalles): float
    {
        $subtotal = 0;

        foreach ($detalles as $detalle) {
            $cantidad = (int) ($detalle['cantidad'] ?? 0);
            $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);

            $subtotal += $cantidad * $precioUnitario;
        }

        return $subtotal;
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}