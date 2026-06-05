<?php

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    /** GET /reservas */
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Reserva::with('mesa');

        if (!empty($params['fecha'])) {
            $query->where('fecha', $params['fecha']);
        }
        if (!empty($params['cliente'])) {
            $query->where('nombre_cliente', 'like', '%' . $params['cliente'] . '%');
        }
        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        return $this->json($response, ['success' => true, 'data' => $query->get()]);
    }

    /** GET /reservas/{id} */
    public function show(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::with('mesa')->find($args['id']);
        if (!$reserva) {
            return $this->json($response, ['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }
        return $this->json($response, ['success' => true, 'data' => $reserva]);
    }

    /** POST /reservas */
    public function store(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $required = ['nombre_cliente', 'telefono_cliente', 'cantidad_personas', 'fecha', 'hora', 'mesa_id'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                return $this->json($response, ['success' => false, 'message' => "El campo {$field} es requerido"], 400);
            }
        }

        if ($body['fecha'] < date('Y-m-d')) {
            return $this->json($response, ['success' => false, 'message' => 'No se permiten reservas en fechas pasadas'], 400);
        }

        $mesa = Mesa::find($body['mesa_id']);
        if (!$mesa) {
            return $this->json($response, ['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        if ($mesa->estado === 'fuera_servicio') {
            return $this->json($response, ['success' => false, 'message' => 'No se puede reservar una mesa fuera de servicio'], 400);
        }

        if ((int)$body['cantidad_personas'] > $mesa->capacidad) {
            return $this->json($response, ['success' => false, 'message' => 'La cantidad de personas supera la capacidad de la mesa'], 400);
        }

        $existe = Reserva::where('mesa_id', $body['mesa_id'])
            ->where('fecha', $body['fecha'])
            ->where('hora', $body['hora'])
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->exists();

        if ($existe) {
            return $this->json($response, ['success' => false, 'message' => 'La mesa ya tiene una reserva en ese horario'], 409);
        }

        $reserva = Reserva::create([
            'nombre_cliente'    => $body['nombre_cliente'],
            'telefono_cliente'  => $body['telefono_cliente'],
            'cantidad_personas' => (int)$body['cantidad_personas'],
            'fecha'             => $body['fecha'],
            'hora'              => $body['hora'],
            'observaciones'     => $body['observaciones'] ?? null,
            'estado'            => 'pendiente',
            'mesa_id'           => (int)$body['mesa_id'],
        ]);

        return $this->json($response, ['success' => true, 'message' => 'Reserva creada', 'data' => $reserva], 201);
    }

    /** PUT /reservas/{id} */
    public function update(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);
        if (!$reserva) {
            return $this->json($response, ['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        $body = $request->getParsedBody();

        if (isset($body['fecha']) && $body['fecha'] < date('Y-m-d')) {
            return $this->json($response, ['success' => false, 'message' => 'No se permiten fechas pasadas'], 400);
        }

        $campos = ['fecha', 'hora', 'mesa_id', 'cantidad_personas', 'observaciones'];
        foreach ($campos as $campo) {
            if (isset($body[$campo])) {
                $reserva->$campo = $body[$campo];
            }
        }
        $reserva->save();

        return $this->json($response, ['success' => true, 'message' => 'Reserva actualizada', 'data' => $reserva]);
    }

    /** PATCH /reservas/{id}/cancelar */
    public function cancelar(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);
        if (!$reserva) {
            return $this->json($response, ['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        return $this->json($response, ['success' => true, 'message' => 'Reserva cancelada', 'data' => $reserva]);
    }
}