<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Rol;
use Illuminate\Support\Facades\Auth;

trait ResuelveUsuario
{
    private function usuarioSesion(): array
    {
        $user = Auth::user();
        $etiqueta = [
            Rol::ADMINISTRADORA => 'Administradora',
            Rol::ENFERMERA => 'Enfermera de turno',
            Rol::OPERADOR => 'Operadora',
            Rol::SECRETARIA => 'Secretaria',
        ][$user->nombreRol()] ?? 'Sin rol';
        return [
            'nombre' => $user->name,
            'rol' => $etiqueta,
        ];
    }
}
