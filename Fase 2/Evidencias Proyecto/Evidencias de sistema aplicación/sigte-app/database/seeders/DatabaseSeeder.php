
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Usuarios y permisos
            RolSeeder::class,
            UserSeeder::class,
            PersonalSeeder::class,

            // Catálogo de cajas e instrumental
            ServicioSeeder::class,
            TipoConjuntoSeeder::class,
            InstrumentoSeeder::class,
            FichaSeeder::class,
            FichaInstrumentoSeeder::class,

            // Inventarios e insumos
            ElementoInventarioSeeder::class,
            InsumoSeeder::class,
        ]);
    }
}
