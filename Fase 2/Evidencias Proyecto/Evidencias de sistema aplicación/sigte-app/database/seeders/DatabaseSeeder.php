<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UserSeeder::class,
            PersonalSeeder::class,
            CajaSeeder::class,
            ConsumoServicioSeeder::class,
            ElementoInventarioSeeder::class,
            InsumoSeeder::class,
            FichaSeeder::class,
        ]);
    }
}
