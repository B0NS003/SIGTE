<?php

namespace Database\Seeders;

use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        $rolId = Rol::query()->where('nombre', Rol::ADMINISTRADORA)->value('id');
        User::query()->updateOrCreate(
            ['email' => 'judith.herrada@sigte.local'],
            [
                'name' => 'Judith Herrada Valladares',
                'password' => 'password',
                'rol_id' => $rolId,
                'role' => Rol::ADMINISTRADORA,
                'activo' => true,
            ]
        );

        $grupos = [
            1 => [
                'CYNTHIA MABEL TRANGOLAO TRANGOLAO',
                'LISSETTE ANDREA LARA DÍAZ',
                'GIULIANA CONSTANZA BARRERA MATTA',
                'FELIPE IGNACIO GONZÁLEZ GAGLIANO',
                'FELIPE ANDRES SAGREDO LISPERGUER',
                'KATHERINE ROSALBA PONCE QUIROZ',
            ],
            2 => [
                'CAMILA FERNANDA MARIPANGUE DONOSO',
                'LADY ROMANETT REYES ANTIÑANCO',
                'BARBARA HERRADA AMPUERO',
                'JAVIERA BEATRIZ GÓMEZ SALDAÑA',
                'KATHERINE  MAGALY VALDIVIA  POBLETE',
                'MACARENA TAMARA ARAYA ARAYA',
                'GABRIELA DE LOS ANGELES CERDA CERDA',
                'GINA MARGOT FLORES SILVA',
            ],
            3 => [
                'ALEJANDRA ANDREA ARROYO MALDONADO',
                'ANA HERMINIA FUENTES ARTIGAS',
                'CLAUDIO ANDRES FRIAS VALDOVINOS',
                'FERNANDA CAROLINA QUIROZ TAPIA',
                'GABRIELA MARION RIQUELME GARCIA',
                'GERALDINE YAHAIRA RAMIREZ ARAVENA',
                'JEANETTE DEL CARMEN CONTRERAS VALDENEGRO',
                'KEITHY CAROLINA PADILLA MALDONADO',
                'LEONOR DEL PILAR HINOJOSA VERA',
                'MARIA SANTIBAÑEZ GARATE',
                'MARTA DE JESUS ALARCON ARAYA',
                'PATRICIO ALEJANDRO TAPIA SILVA',
                'SOLANGE ALEJANDRA ORMEÑO FLORES',
            ],
            4 => [
                'VANESSA BETZABÉ MANRÍQUEZ CONTRERAS',
                'HELGA ABIGAIL GUTIERREZ HANNUS',
                'CLAUDIA PATRICA RIVERA JORQUERA',
                'FLAVIA MACARENA HERMOSILLA ESPARZA',
                'MATÍAS NICOLAS PAVEZ BRAVO',
                'MATÍAS ALEXANDER DONOSO SOTO',
                'NATALIA IGNACIA GÓMEZ SALDAÑA',
                'INES ALEJANDRA JEREZ GONZALEZ',
            ],
            5 => [
                'ALEJANDRA DEL CARMEN PALACIOS VILLEGAS',
            ],
        ];

        $usados = [];
        foreach ($grupos as $grupo => $nombres) {
            foreach ($nombres as $crudo) {
                $nombre = $this->nombrePropio($crudo);
                $email = $this->correo($nombre, $usados);
                $usados[] = $email;
                Personal::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'nombre' => $nombre,
                        'grupo' => $grupo,
                        'activo' => true,
                    ]
                );
                $rolOperadora = Rol::query()->where('nombre', Rol::OPERADOR)->value('id');
                User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $nombre,
                        'password' => 'password',
                        'rol_id' => $rolOperadora,
                        'role' => Rol::OPERADOR,
                        'activo' => true,
                    ]
                );
            }
        }
    }

    private function nombrePropio(string $nombre): string
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'y'];
        $palabras = preg_split('/\s+/u', trim($nombre)) ?: [];
        $salida = [];
        foreach ($palabras as $i => $palabra) {
            $minuscula = mb_strtolower($palabra, 'UTF-8');
            if ($i > 0 && in_array($minuscula, $particulas, true)) {
                $salida[] = $minuscula;
                continue;
            }
            $salida[] = mb_convert_case($minuscula, MB_CASE_TITLE, 'UTF-8');
        }

        return implode(' ', $salida);
    }

    /** @param list<string> $usados */
    private function correo(string $nombre, array $usados): string
    {
        $particulas = ['de', 'del', 'la', 'las', 'los', 'y'];
        $palabras = preg_split('/\s+/u', $nombre) ?: [];
        $primero = $this->slug($palabras[0] ?? 'persona');
        $apellidos = [];
        foreach (array_slice($palabras, 1) as $palabra) {
            if (! in_array(mb_strtolower($palabra, 'UTF-8'), $particulas, true)) {
                $apellidos[] = $this->slug($palabra);
            }
        }
        $base = $primero.'.'.($apellidos[0] ?? 'sigte');
        $correo = $base.'@sigte.local';
        $extra = 1;
        while (in_array($correo, $usados, true) || Personal::query()->where('email', $correo)->where('nombre', '!=', $nombre)->exists()) {
            $sufijo = $apellidos[$extra] ?? (string) ($extra + 1);
            $correo = $base.'.'.$sufijo.'@sigte.local';
            $extra++;
        }

        return $correo;
    }

    private function slug(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);

        return preg_replace('/[^a-z]/', '', $texto) ?: 'persona';
    }
}
