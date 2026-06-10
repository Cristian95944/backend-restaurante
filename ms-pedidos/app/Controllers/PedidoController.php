<?php

namespace App\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PedidoController
{
    private array $estadosPermitidos = [
        'pendiente',
        'en_preparacion',
        'entregado',
        'pagado',
        'cancelado'
    ];

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

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $consulta = Pedido::with('detalles')->orderBy('id', 'desc');

        if (!empty($params['estado'])) {
            $consulta->where('estado', $params['estado']);
        }

        $pedidos = $consulta->get();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Listado de pedidos',
            'pedidos' => $pedidos
        ]);
    }

    public function ver(Request $request, Response $response, array $args): Response
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
            'mensaje' => 'Detalle del pedido',
            'pedido' => $pedido
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $datos = $this->datos($request);

        $mesaId = (int)($datos['mesa_id'] ?? 0);
        $estadoMesa = strtolower(trim($datos['estado_mesa'] ?? ''));
        $productos = $datos['productos'] ?? [];

        if ($mesaId <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Debe seleccionar una mesa válida'
            ], 400);
        }

        if ($estadoMesa === 'disponible') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'No se puede registrar un pedido para una mesa disponible'
            ], 400);
        }

        if (!is_array($productos) || count($productos) === 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El pedido debe tener al menos un producto'
            ], 400);
        }

        $total = 0;
        $detallesPreparados = [];

        foreach ($productos as $producto) {
            $productoId = (int)($producto['producto_id'] ?? 0);
            $nombre = trim($producto['nombre_producto'] ?? '');
            $cantidad = (int)($producto['cantidad'] ?? 0);
            $precio = (float)($producto['precio_unitario'] ?? 0);

            if ($productoId <= 0 || $nombre === '' || $precio <= 0) {
                return $this->json($response, [
                    'estado' => false,
                    'mensaje' => 'Uno de los productos tiene datos incompletos'
                ], 400);
            }

            if ($cantidad < 1) {
                return $this->json($response, [
                    'estado' => false,
                    'mensaje' => 'La cantidad de productos debe ser mayor o igual a uno'
                ], 400);
            }

            $subtotal = $cantidad * $precio;
            $total += $subtotal;

            $detallesPreparados[] = [
                'producto_id' => $productoId,
                'nombre_producto' => $nombre,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $subtotal
            ];
        }

        $pedidoCreado = DB::connection()->transaction(function () use ($mesaId, $total, $detallesPreparados) {
            $pedido = Pedido::create([
                'mesa_id' => $mesaId,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
                'subtotal' => $total,
                'total' => $total,
                'estado' => 'pendiente'
            ]);

            foreach ($detallesPreparados as $detalle) {
                $detalle['pedido_id'] = $pedido->id;
                DetallePedido::create($detalle);
            }

            return $pedido;
        });

        $pedidoConDetalles = Pedido::with('detalles')->find($pedidoCreado->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Pedido registrado correctamente',
            'pedido' => $pedidoConDetalles
        ], 201);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $pedido = Pedido::find($args['id']);

        if (!$pedido) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Pedido no encontrado'
            ], 404);
        }

        $datos = $this->datos($request);
        $nuevoEstado = $datos['estado'] ?? '';

        if (!in_array($nuevoEstado, $this->estadosPermitidos)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de pedido no válido'
            ], 400);
        }

        $pedido->estado = $nuevoEstado;
        $pedido->save();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Estado del pedido actualizado',
            'pedido' => $pedido
        ]);
    }
}