<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'tecnico@sigte.local'],
            [
                'name' => 'Tecnico Demo',
                'password' => 'password',
                'role' => User::ROLE_TECNICO,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'jefatura@sigte.local'],
            [
                'name' => 'Jefatura Demo',
                'password' => 'password',
                'role' => User::ROLE_JEFATURA,
            ]
        );
    }
}