<?php

namespace App\Http\Middleware;

use App\Support\AccesoPorRol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarAccesoPorRol
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
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
