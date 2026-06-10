<?php

namespace App\Controllers;

use App\Models\Categoria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriaController
{
    public function index(Request $request, Response $response)
    {
        $response->getBody()->write(
            Categoria::all()->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }

    public function store(Request $request, Response $response)
    {
        $categoria = Categoria::create(
            $request->getParsedBody()
        );

        $response->getBody()->write(
            $categoria->toJson()
        );

        return $response->withHeader(
            'Content-Type',
            'application/json'
        );
    }
}