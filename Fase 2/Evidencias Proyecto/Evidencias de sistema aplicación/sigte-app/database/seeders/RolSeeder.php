<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => Rol::ADMINISTRADORA,
                'descripcion' => 'Gestión completa del sistema, incluida administración de usuarios.',
            ],
            [
                'nombre' => Rol::ENFERMERA,
                'descripcion' => 'Supervisión de turno y operación; sin gestión de usuarios.',
            ],
            [
                'nombre' => Rol::OPERADOR,
                'descripcion' => 'Operación diaria del ciclo de esterilización.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::query()->updateOrCreate(
                ['nombre' => $rol['nombre']],
                ['descripcion' => $rol['descripcion']]
            );
        }
    }
}
