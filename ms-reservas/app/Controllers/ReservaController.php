<?php

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(
            Reserva::all()->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function show(Request $request, Response $response, array $args)
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Reserva no encontrada'
                ])
            );

            return $response->withStatus(404);
        }

        $response->getBody()->write(
            $reserva->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        $mesa = Mesa::find($data['mesa_id']);

        if (!$mesa) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Mesa inexistente'
                ])
            );

            return $response->withStatus(400);
        }

        if (
            $data['cantidad_personas']
            > $mesa->capacidad
        ) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Capacidad excedida'
                ])
            );

            return $response->withStatus(400);
        }

        $ocupada = Reserva::where(
            'mesa_id',
            $data['mesa_id']
        )
        ->where(
            'fecha',
            $data['fecha']
        )
        ->where(
            'hora',
            $data['hora']
        )
        ->first();

        if ($ocupada) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Mesa ya reservada'
                ])
            );

            return $response->withStatus(400);
        }

        $reserva = Reserva::create($data);

        $response->getBody()->write(
            $reserva->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function destroy(Request $request, Response $response, array $args)
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {

            $response->getBody()->write(
                json_encode([
                    'error' => 'Reserva no encontrada'
                ])
            );

            return $response->withStatus(404);
        }

        $reserva->delete();

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