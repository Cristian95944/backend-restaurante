<?php

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    /** GET /mesas */
    public function index(Request $request, Response $response): Response
    {
        $mesas = Mesa::all();
        return $this->json($response, ['success' => true, 'data' => $mesas]);
    }

    /** GET /mesas/{id} */
    public function show(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);
        if (!$mesa) {
            return $this->json($response, ['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }
        return $this->json($response, ['success' => true, 'data' => $mesa]);
    }

    /** POST /mesas */
    public function store(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        if (empty($body['numero']) || empty($body['capacidad'])) {
            return $this->json($response, ['success' => false, 'message' => 'Número y capacidad son requeridos'], 400);
        }

        if ((int)$body['capacidad'] <= 0) {
            return $this->json($response, ['success' => false, 'message' => 'La capacidad debe ser mayor a cero'], 400);
        }

        if (Mesa::where('numero', $body['numero'])->exists()) {
            return $this->json($response, ['success' => false, 'message' => 'El número de mesa ya existe'], 409);
        }

        $mesa = Mesa::create([
            'numero'    => $body['numero'],
            'capacidad' => (int)$body['capacidad'],
            'estado'    => $body['estado'] ?? 'disponible',
        ]);

        return $this->json($response, ['success' => true, 'message' => 'Mesa creada', 'data' => $mesa], 201);
    }

    /** PUT /mesas/{id} */
    public function update(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);
        if (!$mesa) {
            return $this->json($response, ['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        $body = $request->getParsedBody();

        if (isset($body['capacidad']) && (int)$body['capacidad'] <= 0) {
            return $this->json($response, ['success' => false, 'message' => 'La capacidad debe ser mayor a cero'], 400);
        }

        if (isset($body['capacidad'])) $mesa->capacidad = (int)$body['capacidad'];
        if (isset($body['estado'])) $mesa->estado = $body['estado'];
        $mesa->save();

        return $this->json($response, ['success' => true, 'message' => 'Mesa actualizada', 'data' => $mesa]);
    }

    /** PATCH /mesas/{id}/estado */
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);
        if (!$mesa) {
            return $this->json($response, ['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        $body   = $request->getParsedBody();
        $estados = ['disponible', 'reservada', 'ocupada', 'fuera_servicio'];

        if (!in_array($body['estado'] ?? '', $estados)) {
            return $this->json($response, ['success' => false, 'message' => 'Estado inválido'], 400);
        }

        $mesa->estado = $body['estado'];
        $mesa->save();

        return $this->json($response, ['success' => true, 'message' => 'Estado actualizado', 'data' => $mesa]);
    }
}