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
        View::composer('flujo.*', function ($view): void {
            $user = Auth::user();
            if ($user === null || $user->nombreRol() !== Rol::OPERADOR) {
                $view->with('puesto', null);
                $view->with('nomina', collect());

                return;
            }
            $turno = Personal::turnoVigente();
            $porCorreo = Personal::query()->get()->keyBy('email');
            $nomina = User::query()
                ->where('activo', true)
                ->where('email', '!=', User::CUENTA_PUESTO)
                ->whereHas('rol', fn ($q) => $q->where('nombre', Rol::OPERADOR))
                ->get()
                ->sort(function (User $a, User $b) use ($porCorreo, $turno): int {
                    $enTurno = function (User $user) use ($porCorreo, $turno): int {
                        return ($porCorreo->get($user->email)?->turno === $turno) ? 0 : 1;
                    };

                    return [$enTurno($a), mb_strtolower($a->name)] <=> [$enTurno($b), mb_strtolower($b->name)];
                })
                ->values();
            $view->with('puesto', User::quienRegistra());
            $view->with('nomina', $nomina);
            $view->with('turnoVigente', $turno);
            $view->with('idsDelTurno', $nomina
                ->filter(fn (User $user) => $porCorreo->get($user->email)?->turno === $turno)
                ->pluck('id')
                ->all());
        });
    }
}
