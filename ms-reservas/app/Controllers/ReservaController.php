<?php

namespace App\Controllers;

use App\Models\Mesa;
use App\Models\Reserva;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservaController
{
    private array $estadosReserva = [
        'pendiente',
        'confirmada',
        'cancelada',
        'finalizada'
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

    private function fechaPasada(string $fecha): bool
    {
        return $fecha < date('Y-m-d');
    }

    private function existeCruceReserva(int $mesaId, string $fecha, string $hora, ?int $reservaId = null): bool
    {
        $consulta = Reserva::where('mesa_id', $mesaId)
            ->where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('estado', '!=', 'cancelada');

        if ($reservaId !== null) {
            $consulta->where('id', '!=', $reservaId);
        }

        return $consulta->exists();
    }

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $consulta = Reserva::with('mesa')->orderBy('id', 'desc');

        if (!empty($params['fecha'])) {
            $consulta->where('fecha', $params['fecha']);
        }

        if (!empty($params['cliente'])) {
            $consulta->where('nombre_cliente', 'LIKE', '%' . $params['cliente'] . '%');
        }

        if (!empty($params['estado'])) {
            $consulta->where('estado', $params['estado']);
        }

        $reservas = $consulta->get();

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Listado de reservas',
            'reservas' => $reservas
        ]);
    }

    public function ver(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::with('mesa')->find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Reserva no encontrada'
            ], 404);
        }

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Información de la reserva',
            'reserva' => $reserva
        ]);
    }

    public function crear(Request $request, Response $response): Response
    {
        $datos = $this->datos($request);

        $nombreCliente = trim($datos['nombre_cliente'] ?? '');
        $telefonoCliente = trim($datos['telefono_cliente'] ?? '');
        $cantidadPersonas = (int)($datos['cantidad_personas'] ?? 0);
        $fecha = trim($datos['fecha'] ?? '');
        $hora = trim($datos['hora'] ?? '');
        $observaciones = trim($datos['observaciones'] ?? '');
        $mesaId = (int)($datos['mesa_id'] ?? 0);
        $estado = $datos['estado'] ?? 'pendiente';

        if ($nombreCliente === '' || $telefonoCliente === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El nombre y teléfono del cliente son obligatorios'
            ], 400);
        }

        if ($cantidadPersonas <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad de personas debe ser mayor a cero'
            ], 400);
        }

        if ($fecha === '' || $hora === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La fecha y la hora son obligatorias'
            ], 400);
        }

        if ($this->fechaPasada($fecha)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'No se permiten reservas en fechas pasadas'
            ], 400);
        }

        if (!in_array($estado, $this->estadosReserva)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de reserva no válido'
            ], 400);
        }

        $mesa = Mesa::find($mesaId);

        if (!$mesa) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La mesa seleccionada no existe'
            ], 404);
        }

        if ($mesa->estado === 'fuera_servicio') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'No se puede reservar una mesa fuera de servicio'
            ], 400);
        }

        if ($cantidadPersonas > $mesa->capacidad) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad de personas supera la capacidad de la mesa'
            ], 400);
        }

        if ($this->existeCruceReserva($mesaId, $fecha, $hora)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La mesa ya tiene una reserva en esa fecha y hora'
            ], 400);
        }

        $reserva = Reserva::create([
            'nombre_cliente' => $nombreCliente,
            'telefono_cliente' => $telefonoCliente,
            'cantidad_personas' => $cantidadPersonas,
            'fecha' => $fecha,
            'hora' => $hora,
            'observaciones' => $observaciones,
            'estado' => $estado,
            'mesa_id' => $mesaId
        ]);

        if ($estado !== 'cancelada') {
            $mesa->estado = 'reservada';
            $mesa->save();
        }

        $reserva = Reserva::with('mesa')->find($reserva->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Reserva registrada correctamente',
            'reserva' => $reserva
        ], 201);
    }

    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Reserva no encontrada'
            ], 404);
        }

        $datos = $this->datos($request);

        $nombreCliente = trim($datos['nombre_cliente'] ?? $reserva->nombre_cliente);
        $telefonoCliente = trim($datos['telefono_cliente'] ?? $reserva->telefono_cliente);
        $cantidadPersonas = (int)($datos['cantidad_personas'] ?? $reserva->cantidad_personas);
        $fecha = trim($datos['fecha'] ?? $reserva->fecha);
        $hora = trim($datos['hora'] ?? $reserva->hora);
        $observaciones = trim($datos['observaciones'] ?? $reserva->observaciones);
        $mesaId = (int)($datos['mesa_id'] ?? $reserva->mesa_id);

        if ($nombreCliente === '' || $telefonoCliente === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'El nombre y teléfono del cliente son obligatorios'
            ], 400);
        }

        if ($cantidadPersonas <= 0) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad de personas debe ser mayor a cero'
            ], 400);
        }

        if ($fecha === '' || $hora === '') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La fecha y la hora son obligatorias'
            ], 400);
        }

        if ($this->fechaPasada($fecha)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'No se permiten reservas en fechas pasadas'
            ], 400);
        }

        $mesa = Mesa::find($mesaId);

        if (!$mesa) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La mesa seleccionada no existe'
            ], 404);
        }

        if ($mesa->estado === 'fuera_servicio') {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'No se puede reservar una mesa fuera de servicio'
            ], 400);
        }

        if ($cantidadPersonas > $mesa->capacidad) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La cantidad de personas supera la capacidad de la mesa'
            ], 400);
        }

        if ($this->existeCruceReserva($mesaId, $fecha, $hora, (int)$reserva->id)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'La mesa ya tiene una reserva en esa fecha y hora'
            ], 400);
        }

        $reserva->nombre_cliente = $nombreCliente;
        $reserva->telefono_cliente = $telefonoCliente;
        $reserva->cantidad_personas = $cantidadPersonas;
        $reserva->fecha = $fecha;
        $reserva->hora = $hora;
        $reserva->observaciones = $observaciones;
        $reserva->mesa_id = $mesaId;
        $reserva->save();

        $reservaActualizada = Reserva::with('mesa')->find($reserva->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Reserva actualizada correctamente',
            'reserva' => $reservaActualizada
        ]);
    }

    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::with('mesa')->find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Reserva no encontrada'
            ], 404);
        }

        $datos = $this->datos($request);
        $estado = $datos['estado'] ?? '';

        if (!in_array($estado, $this->estadosReserva)) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Estado de reserva no válido'
            ], 400);
        }

        $reserva->estado = $estado;
        $reserva->save();

        if ($reserva->mesa) {
            if ($estado === 'cancelada' || $estado === 'finalizada') {
                $reserva->mesa->estado = 'disponible';
            } else {
                $reserva->mesa->estado = 'reservada';
            }

            $reserva->mesa->save();
        }

        $reservaActualizada = Reserva::with('mesa')->find($reserva->id);

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Estado de reserva actualizado',
            'reserva' => $reservaActualizada
        ]);
    }

    public function cancelar(Request $request, Response $response, array $args): Response
    {
        $reserva = Reserva::with('mesa')->find($args['id']);

        if (!$reserva) {
            return $this->json($response, [
                'estado' => false,
                'mensaje' => 'Reserva no encontrada'
            ], 404);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        if ($reserva->mesa) {
            $reserva->mesa->estado = 'disponible';
            $reserva->mesa->save();
        }

        return $this->json($response, [
            'estado' => true,
            'mensaje' => 'Reserva cancelada correctamente',
            'reserva' => Reserva::with('mesa')->find($reserva->id)
        ]);
    }
}