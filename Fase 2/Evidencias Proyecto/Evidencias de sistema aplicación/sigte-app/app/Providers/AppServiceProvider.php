<?php

namespace App\Providers;

use App\Models\Personal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['flujo.*', 'flujo.partials.*'], function ($view): void {
            $turno = Personal::turnoVigente();
            $user = Auth::user();
            if ($user === null || $user->nombreRol() !== Rol::OPERADOR) {
                $view->with('puesto', null);
                $view->with('nomina', collect());
                $view->with('turnoVigente', $turno);
                $view->with('idsDelTurno', []);

                return;
            }

            $porUserId = Personal::query()
                ->whereNotNull('user_id')
                ->get()
                ->keyBy('user_id');
            $porCorreo = Personal::query()->get()->keyBy(
                fn (Personal $p) => mb_strtolower((string) $p->email)
            );

            $turnoDe = function (User $operadora) use ($porUserId, $porCorreo): ?string {
                $porId = $porUserId->get($operadora->id);
                if ($porId !== null) {
                    return $porId->turno;
                }

                return $porCorreo->get(mb_strtolower($operadora->email))?->turno;
            };

            $nomina = User::query()
                ->where('activo', true)
                ->where('email', '!=', User::CUENTA_PUESTO)
                ->whereHas('rol', fn ($q) => $q->where('nombre', Rol::OPERADOR))
                ->get()
                ->sort(function (User $a, User $b) use ($turnoDe, $turno): int {
                    $enTurno = fn (User $u): int => ($turnoDe($u) === $turno) ? 0 : 1;

                    return [$enTurno($a), mb_strtolower($a->name)] <=> [$enTurno($b), mb_strtolower($b->name)];
                })
                ->values();

            $view->with('puesto', User::quienRegistra());
            $view->with('nomina', $nomina);
            $view->with('turnoVigente', $turno);
            $view->with('idsDelTurno', $nomina
                ->filter(fn (User $operadora) => $turnoDe($operadora) === $turno)
                ->pluck('id')
                ->all());
        });
    }
}
