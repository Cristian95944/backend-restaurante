<?php

namespace App\Controllers;

use App\Models\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesaController
{
    private array $estadosMesa = [
        'disponible',
        'reservada',
        'ocupada',
        'fuera_servicio'
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

        $consulta = Mesa::orderBy('id', 'asc');

        if (!empty($params['estado'])) {
            $consulta->where('estado', $params['estado']);
        }

        $mesas = $consulta->get();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Listado de mesas',
            'mesas' => $mesas
        ]);
    }

    public function ver(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Mesa no encontrada'
            ], 404);
        }

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Información de la mesa',
            'mesa' => $mesa
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $datos = $this->datos($request);

        $numero = trim($datos['numero'] ?? '');
        $capacidad = (int)($datos['capacidad'] ?? 0);
        $estado = $datos['estado'] ?? 'disponible';

        if ($numero === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El número o nombre de la mesa es obligatorio'
            ], 400);
        }

        if ($capacidad <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La capacidad debe ser mayor a cero'
            ], 400);
        }

        if (!in_array($estado, $this->estadosMesa)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de mesa no válido'
            ], 400);
        }

        $existe = Mesa::where('numero', $numero)->exists();

        if ($existe) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Ya existe una mesa con ese número o nombre'
            ], 400);
        }

        $mesa = Mesa::create([
            'numero' => $numero,
            'capacidad' => $capacidad,
            'estado' => $estado
        ]);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Mesa registrada correctamente',
            'mesa' => $mesa
        ], 201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Mesa no encontrada'
            ], 404);
        }

        $datos = $this->datos($request);

        $numero = trim($datos['numero'] ?? $mesa->numero);
        $capacidad = (int)($datos['capacidad'] ?? $mesa->capacidad);
        $estado = $datos['estado'] ?? $mesa->estado;

        if ($numero === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El número o nombre de la mesa es obligatorio'
            ], 400);
        }

        if ($capacidad <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La capacidad debe ser mayor a cero'
            ], 400);
        }

        if (!in_array($estado, $this->estadosMesa)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de mesa no válido'
            ], 400);
        }

        $mesaDuplicada = Mesa::where('numero', $numero)
            ->where('id', '!=', $mesa->id)
            ->exists();

        if ($mesaDuplicada) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Ya existe otra mesa con ese número o nombre'
            ], 400);
        }

        $mesa->numero = $numero;
        $mesa->capacidad = $capacidad;
        $mesa->estado = $estado;
        $mesa->save();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Mesa actualizada correctamente',
            'mesa' => $mesa
        ]);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['id']);

        if (!$mesa) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Mesa no encontrada'
            ], 404);
        }

        $datos = $this->datos($request);
        $estado = $datos['estado'] ?? '';

        if (!in_array($estado, $this->estadosMesa)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de mesa no válido'
            ], 400);
        }

        $mesa->estado = $estado;
        $mesa->save();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Estado de mesa actualizado',
            'mesa' => $mesa
        ]);
    }
}