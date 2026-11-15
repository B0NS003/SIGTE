<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Guarda fotos de catálogo/inventario en disco público, redimensionadas y comprimidas.
 */
class AlmacenImagenes
{
    public const ANCHO_MAX = 1600;

    public const ALTO_MAX = 1600;

    public const CALIDAD_JPEG = 82;

    public const CALIDAD_WEBP = 80;

    /**
     * @return array{ruta: string, mime: string, url: string}
     */
    public static function guardar(UploadedFile $archivo, string $carpeta, ?string $prefijo = null): array
    {
        $carpeta = trim($carpeta, '/');
        if ($carpeta === '' || str_contains($carpeta, '..')) {
            throw new RuntimeException('Carpeta de imagen no válida.');
        }

        $binario = self::optimizar($archivo);
        $extension = $binario['extension'];
        $mime = $binario['mime'];
        $nombre = ($prefijo ? Str::slug($prefijo).'-' : '')
            .now()->format('YmdHis')
            .'-'
            .Str::lower(Str::random(8))
            .'.'
            .$extension;

        $rutaRelativa = $carpeta.'/'.$nombre;
        Storage::disk('public')->put('uploads/'.$rutaRelativa, $binario['datos']);

        return [
            'ruta' => 'uploads/'.$rutaRelativa,
            'mime' => $mime,
            'url' => asset('storage/uploads/'.$rutaRelativa),
        ];
    }

    public static function url(?string $ruta): ?string
    {
        if ($ruta === null || $ruta === '') {
            return null;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        if (str_starts_with($ruta, 'images/')) {
            return asset($ruta);
        }

        return asset('storage/'.ltrim($ruta, '/'));
    }

    public static function borrar(?string $ruta): void
    {
        if ($ruta === null || $ruta === '' || str_starts_with($ruta, 'images/')) {
            return;
        }

        $relativa = str_starts_with($ruta, 'uploads/') ? $ruta : 'uploads/'.ltrim($ruta, '/');
        Storage::disk('public')->delete($relativa);
    }

    /**
     * @return array{datos: string, mime: string, extension: string}
     */
    private static function optimizar(UploadedFile $archivo): array
    {
        $rutaTemporal = $archivo->getRealPath();
        if ($rutaTemporal === false) {
            throw new RuntimeException('No se pudo leer la imagen subida.');
        }

        $mimeOrigen = (string) ($archivo->getMimeType() ?: '');
        $contenido = file_get_contents($rutaTemporal);
        if ($contenido === false || $contenido === '') {
            throw new RuntimeException('La imagen subida está vacía.');
        }

        if (! function_exists('imagecreatefromstring')) {
            return [
                'datos' => $contenido,
                'mime' => in_array($mimeOrigen, ['image/jpeg', 'image/png', 'image/webp'], true)
                    ? $mimeOrigen
                    : 'image/jpeg',
                'extension' => match ($mimeOrigen) {
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg',
                },
            ];
        }

        $origen = @imagecreatefromstring($contenido);
        if ($origen === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $escala = min(1, self::ANCHO_MAX / max(1, $ancho), self::ALTO_MAX / max(1, $alto));
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        $transparente = imagecolorallocatealpha($destino, 0, 0, 0, 127);
        imagefilledrectangle($destino, 0, 0, $nuevoAncho, $nuevoAlto, $transparente);
        imagecopyresampled($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

        $usaWebp = function_exists('imagewebp') && $mimeOrigen === 'image/webp';
        ob_start();
        if ($usaWebp) {
            imagewebp($destino, null, self::CALIDAD_WEBP);
            $mime = 'image/webp';
            $extension = 'webp';
        } elseif ($mimeOrigen === 'image/png' && self::pngConTransparencia($destino)) {
            imagepng($destino, null, 6);
            $mime = 'image/png';
            $extension = 'png';
        } else {
            imagejpeg($destino, null, self::CALIDAD_JPEG);
            $mime = 'image/jpeg';
            $extension = 'jpg';
        }
        $datos = (string) ob_get_clean();

        imagedestroy($origen);
        imagedestroy($destino);

        // Si la versión optimizada no ahorra, conserva el original.
        if (strlen($datos) >= strlen($contenido) && $escala >= 0.999) {
            return [
                'datos' => $contenido,
                'mime' => in_array($mimeOrigen, ['image/jpeg', 'image/png', 'image/webp'], true)
                    ? $mimeOrigen
                    : $mime,
                'extension' => match ($mimeOrigen) {
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg',
                },
            ];
        }

        return [
            'datos' => $datos,
            'mime' => $mime,
            'extension' => $extension,
        ];
    }

    private static function pngConTransparencia(\GdImage $imagen): bool
    {
        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        for ($y = 0; $y < $alto; $y += max(1, (int) floor($alto / 40))) {
            for ($x = 0; $x < $ancho; $x += max(1, (int) floor($ancho / 40))) {
                $color = imagecolorat($imagen, $x, $y);
                if ((($color & 0x7F000000) >> 24) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}
