<?php

use App\Http\Middleware\AsegurarAccesoPorRol;
use App\Http\Middleware\AsegurarUsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'acceso' => AsegurarAccesoPorRol::class,
        ]);
        $middleware->appendToGroup('auth', AsegurarUsuarioActivo::class);
        $middleware->redirectGuestsTo('/login');
        $middleware->trustProxies(at: '*');
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user === null || ! $user->isActivo()) {
                return '/login';
            }

            return '/panel/'.($user->nombreRol() ?? 'operador');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();