<?php

namespace App\Services;

use App\Models\Usuario;

class AuthService
{
    public function login($usuario, $contrasena)
    {
        $user = Usuario::where('usuario', $usuario)
            ->orWhere('correo', $usuario)
            ->first();

        if (!$user) {
            return false;
        }

        if ($user->contrasena !== $contrasena) {
            return false;
        }

        $token = bin2hex(random_bytes(32));

        $user->token = $token;
        $user->sesion_activa = true;
        $user->save();

        return [
            'token' => $token,
            'usuario' => $user
        ];
    }
}