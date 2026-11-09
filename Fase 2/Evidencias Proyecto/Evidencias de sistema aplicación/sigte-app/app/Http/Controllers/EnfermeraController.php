<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\CierreTurno;
use App\Models\User;
use App\Support\Auditoria;
use App\Support\CierreTurnoContexto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnfermeraController extends Controller
{
    use ResuelveUsuario;

    public function cierreTurno(): View
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();
        $ctx = CierreTurnoContexto::para($usuario);

        return view('enfermera.cierre', [
            'usuario' => $this->usuarioSesion(),
            'turno' => $ctx['turno'],
            'relevos' => $ctx['relevos'],
            'resumen' => $ctx['resumen'],
            'alertas' => $ctx['alertas'],
            'pendientes' => $ctx['pendientes'],
            'historial' => $ctx['historial'],
            'puede_cerrar' => $ctx['puede_cerrar'],
            'motivo_bloqueo' => $ctx['motivo_bloqueo'],
            'ya_cerrado' => $ctx['ya_cerrado'],
        ]);
    }

    public function guardarCierreTurno(Request $request): RedirectResponse
    {
        /** @var User|null $usuario */
        $usuario = Auth::user();
        $ctx = CierreTurnoContexto::para($usuario);

        if (! $ctx['puede_cerrar'] || $usuario === null) {
            return back()
                ->withErrors(['cierre' => $ctx['motivo_bloqueo'] ?? 'No puedes registrar el cierre ahora.'])
                ->withInput();
        }

        $datos = $request->validate([
            'relevo_user_id' => ['required', 'integer', 'exists:users,id'],
            'nota' => ['nullable', 'string', 'max:1000'],
            'check_alertas' => ['accepted'],
            'check_criticas' => ['accepted'],
            'check_stock' => ['sometimes', 'boolean'],
        ], [
            'relevo_user_id.required' => 'Indica quién recibe el turno.',
            'relevo_user_id.exists' => 'El relevo seleccionado no es válido.',
            'check_alertas.accepted' => 'Debes confirmar que revisaste las alertas abiertas.',
            'check_criticas.accepted' => 'Debes confirmar que informaste las cajas críticas.',
        ]);

        $relevo = User::query()
            ->whereKey($datos['relevo_user_id'])
            ->where('activo', true)
            ->whereHas('rol', fn ($q) => $q->where('nombre', 'enfermera'))
            ->first();

        if ($relevo === null || $relevo->id === $usuario->id) {
            throw ValidationException::withMessages([
                'relevo_user_id' => 'Elige una enfermera distinta como relevo.',
            ]);
        }

        try {
            $cierre = DB::transaction(function () use ($usuario, $relevo, $datos, $ctx, $request) {
                $existe = CierreTurno::query()
                    ->whereDate('turno_fecha', $ctx['turno_fecha'])
                    ->where('turno_clave', $ctx['turno_clave'])
                    ->lockForUpdate()
                    ->exists();

                if ($existe) {
                    throw ValidationException::withMessages([
                        'cierre' => 'Este bloque ya fue cerrado por otra persona.',
                    ]);
                }

                return CierreTurno::query()->create([
                    'user_id' => $usuario->id,
                    'relevo_user_id' => $relevo->id,
                    'relevo_nombre' => $relevo->name,
                    'turno_clave' => $ctx['turno_clave'],
                    'turno_etiqueta' => $ctx['turno']['bloque'],
                    'turno_fecha' => $ctx['turno_fecha'],
                    'cerrado_at' => now(),
                    'observacion' => $datos['nota'] ?? null,
                    'check_alertas' => true,
                    'check_criticas' => true,
                    'check_stock' => $request->boolean('check_stock'),
                    'snapshot_alertas' => $ctx['alertas'],
                    'snapshot_pendientes' => $ctx['pendientes'],
                    'snapshot_resumen' => $ctx['resumen'],
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['cierre' => 'No fue posible guardar el cierre. Intenta de nuevo.'])
                ->withInput();
        }

        Auditoria::registrar(
            'Registró cierre de turno',
            'turnos',
            $cierre->turno_etiqueta,
            'Relevo '.$cierre->relevo_nombre.' · '.count($ctx['alertas']).' alertas en snapshot',
            $usuario,
        );

        return redirect()
            ->route('mockups.cierre_turno')
            ->with('ok', 'Cierre de turno registrado. Lo recibió '.$cierre->relevo_nombre.'.');
    }
}
