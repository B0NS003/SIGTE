<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Usuarios demo del MVP (contraseña común: password).
     */
    public function run(): void
    {
        $demos = [
            [
                'email' => 'admin@sigte.local',
                'name' => 'Administradora Demo',
                'rol' => Rol::ADMINISTRADORA,
            ],
            [
                'email' => 'enfermera@sigte.local',
                'name' => 'Enfermera de Turno Demo',
                'rol' => Rol::ENFERMERA,
            ],
            [
                'email' => 'operador@sigte.local',
                'name' => 'Operador Demo',
                'rol' => Rol::OPERADOR,
            ],
            [
                'email' => 'secretaria@sigte.local',
                'name' => 'Secretaria Demo',
                'rol' => Rol::SECRETARIA,
            ],
        ];

        foreach ($demos as $demo) {
            $rolId = Rol::query()->where('nombre', $demo['rol'])->value('id');

            User::query()->updateOrCreate(
                ['email' => $demo['email']],
                [
                    'name' => $demo['name'],
                    'password' => 'password',
                    'rol_id' => $rolId,
                    'activo' => true,
                    'role' => $demo['rol'], // compatibilidad con columna legacy
                ]
            );
        }
    }
}
