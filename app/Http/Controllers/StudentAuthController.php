<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    /**
     * Form login mahasiswa untuk mengakses Naja.
     */
    public function showLogin(): View
    {
        return view('auth.naja-login');
    }

    /**
     * Login memakai NIM. Kata sandi wajib memuat NIM mahasiswa.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'max:100'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $nim = trim($validated['nim']);
        $password = $validated['password'];

        $mahasiswa = Mahasiswa::query()->where('nim', $nim)->first();

        if (! $mahasiswa) {
            return back()
                ->withErrors(['nim' => 'NIM tidak terdaftar. Pastikan NIM kamu benar.'])
                ->onlyInput('nim');
        }

        // Sesuai permintaan: kata sandi harus memuat NIM mahasiswa.
        if (! str_contains($password, $nim)) {
            return back()
                ->withErrors(['password' => 'Kata sandi harus memuat NIM kamu (contoh: ' . $nim . ').'])
                ->onlyInput('nim');
        }

        $request->session()->regenerate();

        $request->session()->put([
            'naja.nim' => $mahasiswa->nim,
            'naja.nama' => $mahasiswa->nama,
            'naja.prodi' => $mahasiswa->prodi,
            'chat.nim' => $mahasiswa->nim,
        ]);

        return redirect()
            ->route('naja.chat')
            ->with('success', 'Selamat datang, ' . $mahasiswa->nama . '!');
    }

    /**
     * Keluar dari sesi Naja (bukan sesi admin).
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['naja.nim', 'naja.nama', 'naja.prodi', 'chat.nim', 'chat.pending']);
        $request->session()->regenerateToken();

        return redirect()
            ->route('landing')
            ->with('success', 'Kamu telah keluar dari Naja.');
    }
}
