<?php

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    public function listarReservas(Request $request, Response $response): Response
    {
        $reservas = Reserva::with('mesa')->get();

        return $this->json($response, [
            'success' => true,
            'reservas' => $reservas
        ]);
    }

    public function verReserva(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::with('mesa')->find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        return $this->json($response, [
            'success' => true,
            'reserva' => $reserva
        ]);
    }

    public function crearReserva(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody() ?? [];

        $error = $this->validarReserva($data);

        if ($error) {
            return $this->json($response, [
                'success' => false,
                'message' => $error
            ], 400);
        }

        $mesa = Mesa::find($data['mesa_id']);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La mesa no existe'
            ], 400);
        }

        if ((int) $data['cantidad_personas'] > (int) $mesa->capacidad) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La cantidad de personas supera la capacidad de la mesa'
            ], 400);
        }

        $reservaExistente = Reserva::where('mesa_id', $data['mesa_id'])
            ->where('fecha', $data['fecha'])
            ->where('hora', $data['hora'])
            ->first();

        if ($reservaExistente) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La mesa ya está reservada en esa fecha y hora'
            ], 400);
        }

        $reserva = Reserva::create([
            'nombre_cliente' => $data['nombre_cliente'],
            'telefono_cliente' => $data['telefono_cliente'] ?? null,
            'cantidad_personas' => $data['cantidad_personas'],
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => $data['estado'] ?? 'pendiente',
            'mesa_id' => $data['mesa_id']
        ]);

        return $this->json($response, [
            'success' => true,
            'message' => 'Reserva creada correctamente',
            'reserva' => $reserva
        ], 201);
    }

    public function actualizarReserva(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        $data = $request->getParsedBody() ?? [];

        $mesaId = $data['mesa_id'] ?? $reserva->mesa_id;
        $fecha = $data['fecha'] ?? $reserva->fecha;
        $hora = $data['hora'] ?? $reserva->hora;
        $cantidadPersonas = $data['cantidad_personas'] ?? $reserva->cantidad_personas;

        $mesa = Mesa::find($mesaId);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La mesa no existe'
            ], 400);
        }

        if ((int) $cantidadPersonas > (int) $mesa->capacidad) {
            return $this->json($response, [
                'success' => false,
                'message' => 'La cantidad de personas supera la capacidad de la mesa'
            ], 400);
        }

        $reservaExistente = Reserva::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('id', '!=', $reserva->id)
            ->first();

        if ($reservaExistente) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Ya existe una reserva para esa mesa en esa fecha y hora'
            ], 400);
        }

        $reserva->update($data);

        return $this->json($response, [
            'success' => true,
            'message' => 'Reserva actualizada correctamente',
            'reserva' => $reserva
        ]);
    }

    public function eliminarReserva(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        $reserva->delete();

        return $this->json($response, [
            'success' => true,
            'message' => 'Reserva eliminada correctamente'
        ]);
    }

    public function reservasPorMesa(Request $request, Response $response, array $args): Response
    {
        $mesa = Mesa::find($args['mesa']);

        if (!$mesa) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Mesa no encontrada'
            ], 404);
        }

        $reservas = Reserva::with('mesa')
            ->where('mesa_id', $args['mesa'])
            ->get();

        return $this->json($response, [
            'success' => true,
            'mesa' => $mesa,
            'reservas' => $reservas
        ]);
    }

    private function validarReserva(array $data): ?string
    {
        if (empty($data['nombre_cliente'])) {
            return 'El nombre del cliente es obligatorio';
        }

        if (empty($data['cantidad_personas']) || (int) $data['cantidad_personas'] <= 0) {
            return 'La cantidad de personas debe ser mayor que cero';
        }

        if (empty($data['fecha'])) {
            return 'La fecha de la reserva es obligatoria';
        }

        if (empty($data['hora'])) {
            return 'La hora de la reserva es obligatoria';
        }

        if (empty($data['mesa_id'])) {
            return 'La mesa es obligatoria';
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