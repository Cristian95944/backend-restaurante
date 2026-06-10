<?php

namespace App\Helpers;

class TokenGenerator
{
    public static function crearToken(): string
    {
        return bin2hex(random_bytes(25));
    }
}