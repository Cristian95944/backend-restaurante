<?php

namespace App\Controllers;

use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriaController
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

    public function listar(Request $request, Response $response): Response
    {
        $categorias = Categoria::orderBy('id', 'asc')->get();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Listado de categorías',
            'categorias' => $categorias
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $datos = $this->datos($request);

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');

        if ($nombre === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El nombre de la categoría es obligatorio'
            ], 400);
        }

        $existe = Categoria::where('nombre', $nombre)->exists();

        if ($existe) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La categoría ya existe'
            ], 400);
        }

        $categoria = Categoria::create([
            'nombre' => $nombre,
            'descripcion' => $descripcion
        ]);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Categoría registrada correctamente',
            'categoria' => $categoria
        ], 201);
    }
}