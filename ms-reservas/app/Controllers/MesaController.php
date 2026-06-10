<?php

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    public function listarMesas(Request $request, Response $response): Response
    {
        $mesas = Mesa::all();

        return $this->json($response, [
            'success' => true,
            'mesas' => $mesas
        ]);
    }

    public function verMesa(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'mesa' => $mesa
        ]);
    }

    public function crearMesa(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        if (empty($data['numero'])) {
            return $this->json($response, [
                'success' => false,
                'message' => 'El número de la mesa es obligatorio'
            ], 400);
        }

        if (empty($data['capacidad']) || (int) $data['capacidad'] <= 0) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La capacidad debe ser mayor que cero'
            ], 400);
        }

        $mesaExistente = Mesa::where('numero', $data['numero'])->first();

        if ($mesaExistente) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Ya existe una mesa con ese número'
            ], 400);
        }

        $mesa = Mesa::create([
            'numero' => $data['numero'],
            'capacidad' => $data['capacidad'],
            'estado' => $data['estado'] ?? 'disponible'
        ]);

        return $this->json($response, [
            'success' => true,
            'message' => 'Mesa creada correctamente',
            'mesa' => $mesa
        ], 201);
    }

    public function actualizarMesa(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }

        $data = $request->getParsedBody() ?? [];

        if (isset($data['capacidad']) && (int) $data['capacidad'] <= 0) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La capacidad debe ser mayor que cero'
            ], 400);
        }

        if (isset($data['numero'])) {
            $mesaExistente = Mesa::where('numero', $data['numero'])
                ->where('id', '!=', $mesa->id)
                ->first();

            if ($mesaExistente) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Ya existe otra mesa con ese número'
                ], 400);
            }
        }

        $mesa->update($data);

        return $this->json($response, [
            'success' => true,
            'message' => 'Mesa actualizada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function eliminarMesa(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }

        if ($mesa->reservas()->count() > 0) {
            return $this->json($response, [
                'success' => false,
                'message' => 'No se puede eliminar la mesa porque tiene reservas asociadas'
            ], 400);
        }

        $mesa->delete();

        return $this->json($response, [
            'success' => true,
            'message' => 'Mesa eliminada correctamente'
        ]);
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }
}