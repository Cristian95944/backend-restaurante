<?php

namespace App\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    // ── CATEGORÍAS ──────────────────────────────────────────

    public function getCategorias(Request $request, Response $response): Response
    {
        $categorias = Categoria::all();
        $response->getBody()->write(json_encode($categorias));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function createCategoria(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['nombre'])) {
            $response->getBody()->write(json_encode(['error' => 'El nombre es requerido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $categoria = Categoria::create(['nombre' => $data['nombre']]);
        $response->getBody()->write(json_encode($categoria));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function updateCategoria(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['id']);

        if (!$categoria) {
            $response->getBody()->write(json_encode(['error' => 'Categoría no encontrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();
        if (!empty($data['nombre'])) {
            $categoria->nombre = $data['nombre'];
        }
        $categoria->save();

        $response->getBody()->write(json_encode($categoria));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteCategoria(Request $request, Response $response, array $args): Response
    {
        $categoria = Categoria::find($args['id']);

        if (!$categoria) {
            $response->getBody()->write(json_encode(['error' => 'Categoría no encontrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $categoria->delete();
        $response->getBody()->write(json_encode(['mensaje' => 'Categoría eliminada correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // ── PRODUCTOS ────────────────────────────────────────────

    public function getProductos(Request $request, Response $response): Response
    {
        $productos = Producto::with('categoria')->get();
        $response->getBody()->write(json_encode($productos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::with('categoria')->find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode(['error' => 'Producto no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode($producto));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function getProductosPorCategoria(Request $request, Response $response, array $args): Response
    {
        $productos = Producto::with('categoria')
            ->where('categoria_id', $args['id'])
            ->get();

        $response->getBody()->write(json_encode($productos));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function createProducto(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        if (empty($data['nombre']) || empty($data['precio']) || empty($data['categoria_id'])) {
            $response->getBody()->write(json_encode(['error' => 'nombre, precio y categoria_id son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!Categoria::find($data['categoria_id'])) {
            $response->getBody()->write(json_encode(['error' => 'La categoría no existe']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $producto = Producto::create([
            'nombre'       => $data['nombre'],
            'descripcion'  => $data['descripcion']  ?? null,
            'precio'       => $data['precio'],
            'disponible'   => $data['disponible']   ?? true,
            'categoria_id' => $data['categoria_id'],
        ]);

        $response->getBody()->write(json_encode($producto->load('categoria')));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function updateProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode(['error' => 'Producto no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $data = $request->getParsedBody();

        if (!empty($data['nombre']))       $producto->nombre       = $data['nombre'];
        if (!empty($data['descripcion']))  $producto->descripcion  = $data['descripcion'];
        if (!empty($data['precio']))       $producto->precio       = $data['precio'];
        if (isset($data['disponible']))    $producto->disponible   = $data['disponible'];
        if (!empty($data['categoria_id'])) {
            if (!Categoria::find($data['categoria_id'])) {
                $response->getBody()->write(json_encode(['error' => 'La categoría no existe']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            $producto->categoria_id = $data['categoria_id'];
        }

        $producto->save();

        $response->getBody()->write(json_encode($producto->load('categoria')));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function deleteProducto(Request $request, Response $response, array $args): Response
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {
            $response->getBody()->write(json_encode(['error' => 'Producto no encontrado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $producto->delete();
        $response->getBody()->write(json_encode(['mensaje' => 'Producto eliminado correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}