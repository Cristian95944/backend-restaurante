<?php

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(
            Mesa::all()->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function show(Request $request, Response $response, array $args)
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Mesa no encontrada'
                ])
            );

            return $response->withStatus(404);
        }

        $response->getBody()->write(
            $mesa->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function store(Request $request, Response $response)
    {
        $mesa = Mesa::create(
            $request->getParsedBody()
        );

        $response->getBody()->write(
            $mesa->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function update(Request $request, Response $response, array $args)
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Mesa no encontrada'
                ])
            );

            return $response->withStatus(404);
        }

        $mesa->update(
            $request->getParsedBody()
        );

        $response->getBody()->write(
            $mesa->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Mesa no encontrada'
                ])
            );

            return $response->withStatus(404);
        }

        $mesa->delete();

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