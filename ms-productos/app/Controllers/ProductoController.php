<?php

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
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

    private function convertirDisponible($valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int)$valor === 1;
        }

        $valor = strtolower(trim((string)$valor));

        return in_array($valor, ['true', '1', 'si', 'sí', 'disponible']);
    }

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $consulta = Producto::with('categoria')->orderBy('id', 'desc');

        if (!empty($params['categoria_id'])) {
            $consulta->where('categoria_id', (int)$params['categoria_id']);
        }

        if (isset($params['disponible'])) {
            $consulta->where('disponible', $this->convertirDisponible($params['disponible']));
        }

        if (!empty($params['nombre'])) {
            $consulta->where('nombre', 'LIKE', '%' . $params['nombre'] . '%');
        }

        $productos = $consulta->get();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Listado de productos',
            'productos' => $productos
        ]);
    }

    public function ver(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::with('categoria')->find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Producto no encontrado'
            ], 404);
        }

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Información del producto',
            'producto' => $producto
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $datos = $this->datos($request);

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $precio = (float)($datos['precio'] ?? 0);
        $categoriaId = (int)($datos['categoria_id'] ?? 0);
        $disponible = $this->convertirDisponible($datos['disponible'] ?? true);

        if ($nombre === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El nombre del producto es obligatorio'
            ], 400);
        }

        if ($precio <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El precio debe ser mayor a cero'
            ], 400);
        }

        if ($categoriaId <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Debe seleccionar una categoría válida'
            ], 400);
        }

        $categoria = Categoria::find($categoriaId);

        if (!$categoria) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La categoría seleccionada no existe'
            ], 404);
        }

        $productoRepetido = Producto::where('nombre', $nombre)->exists();

        if ($productoRepetido) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Ya existe un producto con ese nombre'
            ], 400);
        }

        $producto = Producto::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'disponible' => $disponible,
            'categoria_id' => $categoriaId
        ]);

        $producto = Producto::with('categoria')->find($producto->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Producto registrado correctamente',
            'producto' => $producto
        ], 201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Producto no encontrado'
            ], 404);
        }

        $datos = $this->datos($request);

        $nombre = trim($datos['nombre'] ?? $producto->nombre);
        $descripcion = trim($datos['descripcion'] ?? $producto->descripcion);
        $precio = (float)($datos['precio'] ?? $producto->precio);
        $categoriaId = (int)($datos['categoria_id'] ?? $producto->categoria_id);
        $disponible = array_key_exists('disponible', $datos)
            ? $this->convertirDisponible($datos['disponible'])
            : (bool)$producto->disponible;

        if ($nombre === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El nombre del producto es obligatorio'
            ], 400);
        }

        if ($precio <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El precio debe ser mayor a cero'
            ], 400);
        }

        $categoria = Categoria::find($categoriaId);

        if (!$categoria) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La categoría seleccionada no existe'
            ], 404);
        }

        $productoRepetido = Producto::where('nombre', $nombre)
            ->where('id', '!=', $producto->id)
            ->exists();

        if ($productoRepetido) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Ya existe otro producto con ese nombre'
            ], 400);
        }

        $producto->nombre = $nombre;
        $producto->descripcion = $descripcion;
        $producto->precio = $precio;
        $producto->categoria_id = $categoriaId;
        $producto->disponible = $disponible;
        $producto->save();

        $productoActualizado = Producto::with('categoria')->find($producto->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Producto actualizado correctamente',
            'producto' => $productoActualizado
        ]);
    }

    public function eliminar(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Producto no encontrado'
            ], 404);
        }

        $producto->delete();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Producto eliminado correctamente'
        ]);
    }
}