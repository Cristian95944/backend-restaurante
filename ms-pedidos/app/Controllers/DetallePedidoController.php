<?php

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DetallePedidoController
{
    private function json(Response $response, array $datos, int $codigo = 200): Response
    {
        $response->getBody()->write(json_encode($datos, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }

    private function datos(Request $request): array
    {
        $datos = $request->getParsedBody();

        if (!is_array($datos)) {
            $datos = json_decode($request->getBody()->getContents(), true);
        }

        return is_array($datos) ? $datos : [];
    }

    private function recalcularPedido(int $pedidoId): void
    {
        $total = DetallePedido::where('pedido_id', $pedidoId)->sum('subtotal');

        $pedido = Pedido::find($pedidoId);

        if ($pedido) {
            $pedido->subtotal = $total;
            $pedido->total = $total;
            $pedido->save();
        }
    }

    public function listar(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::with('detalles')->find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Pedido no encontrado'
            ], 404);
        }

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Productos del pedido',
            'pedido' => $pedido
        ]);
    }

    public function agregar(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Pedido no encontrado'
            ], 404);
        }

        $datos = $this->datos($request);

        $productoId = (int)($datos['producto_id'] ?? 0);
        $nombre = trim($datos['nombre_producto'] ?? '');
        $cantidad = (int)($datos['cantidad'] ?? 0);
        $precio = (float)($datos['precio_unitario'] ?? 0);

        if ($productoId <= 0 || $nombre === '' || $precio <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Datos del producto incompletos'
            ], 400);
        }

        if ($cantidad < 1) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad debe ser mayor o igual a uno'
            ], 400);
        }

        $detalle = DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $productoId,
            'nombre_producto' => $nombre,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio
        ]);

        $this->recalcularPedido($pedido->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Producto agregado al pedido',
            'detalle' => $detalle,
            'pedido' => Pedido::with('detalles')->find($pedido->id)
        ], 201);
    }

    public function actualizarCantidad(Request $request, Response $response, array $args): Response
    {
        $detalle = DetallePedido::find($args['id']);

        if (!$detalle) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Detalle del pedido no encontrado'
            ], 404);
        }

        $datos = $this->datos($request);
        $cantidad = (int)($datos['cantidad'] ?? 0);

        if ($cantidad < 1) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad debe ser mayor o igual a uno'
            ], 400);
        }

        $detalle->cantidad = $cantidad;
        $detalle->subtotal = $cantidad * $detalle->precio_unitario;
        $detalle->save();

        $this->recalcularPedido($detalle->pedido_id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Cantidad actualizada correctamente',
            'detalle' => $detalle,
            'pedido' => Pedido::with('detalles')->find($detalle->pedido_id)
        ]);
    }

    public function eliminar(Request $request, Response $response, array $args): Response
    {
        $detalle = DetallePedido::find($args['id']);

        if (!$detalle) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Detalle del pedido no encontrado'
            ], 404);
        }

        $pedidoId = $detalle->pedido_id;

        $cantidadDetalles = DetallePedido::where('pedido_id', $pedidoId)->count();

        if ($cantidadDetalles <= 1) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El pedido no puede quedar vacío'
            ], 400);
        }

        $detalle->delete();

        $this->recalcularPedido($pedidoId);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Producto eliminado del pedido',
            'pedido' => Pedido::with('detalles')->find($pedidoId)
        ]);
    }
}