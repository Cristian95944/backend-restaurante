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

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}