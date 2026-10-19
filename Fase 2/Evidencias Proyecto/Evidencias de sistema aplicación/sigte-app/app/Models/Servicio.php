<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Servicio extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
    ];

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'servicio_id');
    }

    public function consumos(): HasMany
    {
        return $this->hasMany(ConsumoServicio::class, 'servicio_id');
    }

    public static function idPorNombre(string $nombre): ?int
    {
        $texto = Str::ascii(Str::lower(trim($nombre)));
        $pistas = [
            'pabellon' => 'pabell',
            'dental' => 'dental',
            'maternidad' => 'matern',
            'urgencia' => 'urgenc',
            'uci' => 'uci',
            'curaciones' => 'curacion',
            'otro' => 'otro',
            'general' => 'general',
        ];
        $codigo = 'general';
        foreach ($pistas as $candidato => $pista) {
            if (str_contains($texto, $pista)) {
                $codigo = $candidato;
                break;
            }
        }

        return static::query()->where('codigo', $codigo)->value('id');
    }
}
