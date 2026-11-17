<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Si alguien abre una página protegida sin haber iniciado sesión, va al inicio de sesión del panel
        $middleware->redirectGuestsTo('/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Consulta del cliente: si alguien prueba códigos demasiadas veces seguidas,
        // se le pide esperar un minuto (en lugar de mostrar una página de error)
        $exceptions->render(function (ThrottleRequestsException $error, Request $request) {
            if ($request->routeIs('consulta.*')) {
                return redirect()->route('consulta.formulario')
                    ->withInput($request->only('folio'))
                    ->withErrors(['codigo' => 'Hiciste demasiados intentos. Espera un minuto y vuelve a intentarlo.']);
            }
        });
    })->create();
