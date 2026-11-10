<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNajaLoggedIn
{
    /**
     * Mahasiswa harus masuk dengan NIM sebelum memakai Naja.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('naja.nim')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'reply' => 'Sesi kamu sudah berakhir. Silakan masuk ulang dengan NIM.',
                    'type' => 'unauthenticated',
                ], 401);
            }

            return redirect()
                ->route('naja.login')
                ->with('error', 'Silakan masuk dengan NIM kamu untuk mengakses Naja.');
        }

        return $next($request);
    }
}
