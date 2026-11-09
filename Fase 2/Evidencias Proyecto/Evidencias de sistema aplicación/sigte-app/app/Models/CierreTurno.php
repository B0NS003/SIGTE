<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreTurno extends Model
{
    protected $table = 'cierres_turno';

    protected $fillable = [
        'user_id',
        'relevo_user_id',
        'relevo_nombre',
        'turno_clave',
        'turno_etiqueta',
        'turno_fecha',
        'cerrado_at',
        'observacion',
        'check_alertas',
        'check_criticas',
        'check_stock',
        'snapshot_alertas',
        'snapshot_pendientes',
        'snapshot_resumen',
    ];

    protected function casts(): array
    {
        return [
            'turno_fecha' => 'date',
            'cerrado_at' => 'datetime',
            'check_alertas' => 'boolean',
            'check_criticas' => 'boolean',
            'check_stock' => 'boolean',
            'snapshot_alertas' => 'array',
            'snapshot_pendientes' => 'array',
            'snapshot_resumen' => 'array',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relevo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relevo_user_id');
    }
}
