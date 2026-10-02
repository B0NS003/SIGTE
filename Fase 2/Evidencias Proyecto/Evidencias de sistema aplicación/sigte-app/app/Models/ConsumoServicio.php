<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ConsumoServicio extends Model
{
    protected $table = 'consumos_servicio';

    protected $fillable = [
        'servicio',
        'periodo',
        'consumo',
        'litros',
        'observacion',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'consumo' => 'integer',
            'litros' => 'decimal:2',
        ];
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return list<string> */
    public static function servicios(): array
    {
        return ['Pabellon', 'Dental', 'Maternidad', 'Urgencia', 'UCI', 'Curaciones'];
    }

    public static function etiquetaPeriodo(Carbon|string|null $periodo): string
    {
        if ($periodo === null) {
            return 'Sin período';
        }

        $fecha = $periodo instanceof Carbon ? $periodo : Carbon::parse($periodo);
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        return ($meses[$fecha->month] ?? $fecha->format('m')).' '.$fecha->year;
    }
}
