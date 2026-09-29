<?php

use App\Http\Middleware\AsegurarAccesoPorRol;
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
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function () {
            $rol = auth()->user()?->nombreRol() ?? 'operador';

            return '/panel/'.$rol;
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();