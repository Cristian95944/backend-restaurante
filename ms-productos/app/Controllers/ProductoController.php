<?php

namespace App\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function listarProductos(Request $request, Response $response): Response
    {
        $productos = Producto::with('categoria')->get();

        return $this->json($response, [
            'success' => true,
            'productos' => $productos
        ]);
    }

    public function verProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::with('categoria')->find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'producto' => $producto
        ]);
    }

    public function crearProducto(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        $error = $this->validarProducto($data);

        if ($error) {
            return $this->json($response, [
                'success' => false,
                'message' => $error
            ], 400);
        }

        $productoExistente = Producto::where('nombre', $data['nombre'])->first();

        if ($productoExistente) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Ya existe un producto con ese nombre'
            ], 400);
        }

        $producto = Producto::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'disponible' => $data['disponible'] ?? true,
            'categoria_id' => $data['categoria_id']
        ]);

        return $this->json($response, [
            'success' => true,
            'message' => 'Producto creado correctamente',
            'producto' => $producto
        ], 201);
    }

    public function actualizarProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $data = $request->getParsedBody() ?? [];

        if (isset($data['precio']) && (float) $data['precio'] <= 0) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El precio debe ser mayor que cero'
            ], 400);
        }

        if (isset($data['categoria_id']) && !Categoria::find($data['categoria_id'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La categoría no existe'
            ], 400);
        }

        $producto->update($data);

        return $this->json($response, [
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'producto' => $producto
        ]);
    }

    public function eliminarProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $producto->delete();

        return $this->json($response, [
            'success' => true,
            'message' => 'Producto eliminado correctamente'
        ]);
    }

    public function productosPorCategoria(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['categoria']);

        if (!$categoria) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $productos = Producto::with('categoria')
            ->where('categoria_id', $args['categoria'])
            ->get();

        return $this->json($response, [
            'success' => true,
            'categoria' => $categoria,
            'productos' => $productos
        ]);
    }

    public function listarCategorias(Request $request, Response $response): Response
    {
        $categorias = Categoria::all();

        return $this->json($response, [
            'success' => true,
            'categorias' => $categorias
        ]);
    }

    public function crearCategoria(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        if (empty($data['nombre'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El nombre de la categoría es obligatorio'
            ], 400);
        }

        $categoriaExistente = Categoria::where('nombre', $data['nombre'])->first();

        if ($categoriaExistente) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Ya existe una categoría con ese nombre'
            ], 400);
        }

        $categoria = Categoria::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'estado' => $data['estado'] ?? true
        ]);

        return $this->json($response, [
            'success' => true,
            'message' => 'Categoría creada correctamente',
            'categoria' => $categoria
        ], 201);
    }

    private function validarProducto(array $data): ?string
    {
        if (empty($data['nombre'])) {
            return 'El nombre del producto es obligatorio';
        }

        if (!isset($data['precio']) || (float) $data['precio'] <= 0) {
            return 'El precio debe ser mayor que cero';
        }

        if (empty($data['categoria_id'])) {
            return 'La categoría es obligatoria';
        }

        if (!Categoria::find($data['categoria_id'])) {
            return 'La categoría no existe';
        }

        return null;
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}