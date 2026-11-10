<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNajaLoggedIn
{
    /**
     * Mahasiswa yang sudah masuk tidak perlu melihat form login lagi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('naja.nim')) {
            return redirect()->route('naja.chat');
        }

        return $next($request);
    }
}
