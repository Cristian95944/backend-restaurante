<?php

namespace App\Controllers;

use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductoController
{
    public function index(Request $request, Response $response)
    {
        $productos = Producto::all();

        $response->getBody()->write(
            $productos->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function show(Request $request, Response $response, array $args)
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Producto no encontrado'
                ])
            );

            return $response->withStatus(404);
        }

        $response->getBody()->write(
            $producto->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['nombre'])) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Nombre requerido'
                ])
            );

            return $response->withStatus(400);
        }

        if ($data['precio'] <= 0) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Precio inválido'
                ])
            );

            return $response->withStatus(400);
        }

        $existe = Producto::where(
            'nombre',
            $data['nombre']
        )->first();

        if ($existe) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Producto ya existe'
                ])
            );

            return $response->withStatus(400);
        }

        $producto = Producto::create($data);

        $response->getBody()->write(
            $producto->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function update(Request $request, Response $response, array $args)
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Producto no encontrado'
                ])
            );

            return $response->withStatus(404);
        }

        $producto->update(
            $request->getParsedBody()
        );

        $response->getBody()->write(
            $producto->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $producto = Producto::find($args['id']);

        if (!$producto) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Producto no encontrado'
                ])
            );

            return $response->withStatus(404);
        }

        $producto->delete();

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

    public function porCategoria(Request $request, Response $response, array $args)
    {
        $productos = Producto::where(
            'categoria_id',
            $args['categoria']
        )->get();

        $response->getBody()->write(
            $productos->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }
}