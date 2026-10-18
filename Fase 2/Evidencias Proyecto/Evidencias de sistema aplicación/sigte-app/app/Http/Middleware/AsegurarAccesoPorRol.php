<?php

namespace App\Http\Middleware;

use App\Support\AccesoPorRol;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AsegurarAccesoPorRol
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && ! $user->isActivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Esta cuenta está desactivada. Pide a la jefatura que la reactive.',
            ]);
        }

        $ruta = $request->route()?->getName();

        $permitido = $ruta === 'mockups.panel'
            ? AccesoPorRol::puedeVerPanel($user, (string) $request->route('rol'))
            : AccesoPorRol::puedeAbrirRuta($user, $ruta);

        if (! $permitido) {
            return redirect()->route('mockups.panel', $user->nombreRol());
        }

        return $next($request);
    }
}
