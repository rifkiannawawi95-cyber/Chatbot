<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang membuka /admin diarahkan ke form login admin,
        // bukan ke route "login" bawaan yang tidak ada.
        $middleware->redirectGuestsTo(fn (Request $request) => route('admin.login'));

        // User yang sudah login tidak perlu melihat form login lagi.
        $middleware->redirectUsersTo(fn (Request $request) => route('admin.dashboard'));

        // Middleware sesi login mahasiswa untuk aplikasi Naja.
        $middleware->alias([
            'naja.auth' => \App\Http\Middleware\EnsureNajaLoggedIn::class,
            'naja.guest' => \App\Http\Middleware\RedirectIfNajaLoggedIn::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
