<?php

namespace App\Support;

use App\Models\RegistroAuditoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Throwable;

class Auditoria
{
    public static function registrar(
        string $accion,
        string $tipo,
        ?string $objeto = null,
        ?string $detalle = null,
        ?User $actor = null,
    ): void {
        try {
            $quien = $actor ?? User::quienRegistra() ?? Auth::user();

            RegistroAuditoria::query()->create([
                'user_id' => $quien instanceof User ? $quien->id : null,
                'accion' => mb_substr($accion, 0, 120),
                'tipo' => mb_substr($tipo, 0, 40),
                'objeto' => $objeto !== null ? mb_substr($objeto, 0, 120) : null,
                'detalle' => $detalle !== null ? mb_substr($detalle, 0, 255) : null,
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
