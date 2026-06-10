<?php

namespace App\Controllers;

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

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}