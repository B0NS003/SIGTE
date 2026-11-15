<?php

namespace App\Support;

/**
 * Paleta compartida admin/consulta para cabeceras por servicio.
 * El hash replica el de public/js/modulos/catalogo/admin.js.
 */
class CatalogoColores
{
    /** @var list<string> */
    public const PALETA = ['#398ff3', '#f5bc40', '#f48cac', '#a877ec', '#36b8a5', '#f18a51'];

    public static function paraServicio(string $nombre): string
    {
        $clave = mb_strtolower($nombre);
        $hash = 0;
        $largo = mb_strlen($clave);
        for ($i = 0; $i < $largo; $i++) {
            $hash = (($hash << 5) - $hash) + mb_ord(mb_substr($clave, $i, 1));
            $hash = unpack('l', pack('l', $hash))[1];
        }

        return self::PALETA[abs($hash) % count(self::PALETA)];
    }
}
