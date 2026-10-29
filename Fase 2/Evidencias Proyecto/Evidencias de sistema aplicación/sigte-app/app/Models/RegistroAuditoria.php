<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAuditoria extends Model
{
    protected $table = 'registros_auditoria';

    public const TIPOS = [
        'etapa' => 'Etapas del flujo',
        'entrega' => 'Entregas',
        'usuarios' => 'Usuarios',
        'inventario' => 'Inventario',
        'turnos' => 'Turnos',
    ];

    protected $fillable = [
        'user_id',
        'accion',
        'tipo',
        'objeto',
        'detalle',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marcaPeriodo(): string
    {
        $momento = $this->created_at?->timezone('America/Santiago');
        if ($momento === null) {
            return 'mes';
        }

        $hoy = now()->timezone('America/Santiago')->startOfDay();
        if ($momento->copy()->startOfDay()->equalTo($hoy)) {
            return 'hoy';
        }
        if ($momento->copy()->startOfDay()->equalTo($hoy->copy()->subDay())) {
            return 'ayer';
        }
        if ($momento->greaterThanOrEqualTo($hoy->copy()->subDays(6))) {
            return 'semana';
        }

        return 'mes';
    }

    /** @return array{fecha: string, hora: string, cuando: string, actor: string, accion: string, tipo: string, objeto: string, detalle: string, user_id: ?int} */
    public function paraConsulta(): array
    {
        $momento = $this->created_at->timezone('America/Santiago');

        return [
            'fecha' => $momento->format('d/m/Y'),
            'hora' => $momento->format('H:i'),
            'cuando' => $this->marcaPeriodo(),
            'actor' => $this->actor?->name ?? 'Usuario eliminado',
            'accion' => $this->accion,
            'tipo' => $this->tipo,
            'objeto' => $this->objeto ?? '—',
            'detalle' => $this->detalle ?? '',
            'user_id' => $this->user_id,
        ];
    }
}
