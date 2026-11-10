<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\StudentAuthController;
use App\Services\UnujaSsoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// ============================================================
// LANDING PAGE PUBLIK
// ============================================================
Route::get('/', function () {
    return view('landing', [
        'totalMahasiswa' => \App\Models\Mahasiswa::count(),
        'totalProdi' => \App\Models\Mahasiswa::query()->whereNotNull('prodi')->distinct()->count('prodi'),
    ]);
})->name('landing');

// ============================================================
// LOGIN MAHASISWA (NIM + kata sandi yang memuat NIM)
// ============================================================
Route::middleware('naja.guest')->group(function () {
    Route::get('/masuk', [StudentAuthController::class, 'showLogin'])->name('naja.login');
    Route::post('/masuk', [StudentAuthController::class, 'login'])->name('naja.login.attempt');
});

// ============================================================
// APLIKASI NAJA (wajib login NIM)
// ============================================================
Route::middleware('naja.auth')->group(function () {
    Route::get('/naja', function () {
        $mahasiswaLogin = \App\Models\Mahasiswa::query()
            ->where('nim', session('naja.nim'))
            ->first();

        if ($mahasiswaLogin) {
            session([
                'naja.nama' => $mahasiswaLogin->nama,
                'naja.prodi' => $mahasiswaLogin->prodi,
            ]);
        }

        return view('welcome', ['mahasiswaLogin' => $mahasiswaLogin]);
    })->name('naja.chat');

    Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/keluar', [StudentAuthController::class, 'logout'])->name('naja.logout');
});

// ============================================================
// LANGKAH 1: Otorisasi - TAMPILKAN RESPONS LENGKAP
// Buka: http://chatbot-.test/langkah/1-authorize
// ============================================================
Route::get('/langkah/1-authorize', function (UnujaSsoService $sso) {
    $result = $sso->authorize();

    // Baca debug file yang disimpan untuk lihat SEMUA data
    $debugFile = storage_path('app/sso_debug_authorize.json');
    $debugData = file_exists($debugFile) ? json_decode(file_get_contents($debugFile), true) : null;

    return response()->json([
        'langkah' => '1. Otorisasi ke SSO UNUJA',
        'url_dipanggil' => 'POST https://sso.unuja.ac.id/portal/data/authorize',
        'token_berhasil_diekstrak' => $result['token_extracted'] ?? null,
        'response_headers' => $debugData['response_headers'] ?? 'file debug belum ada',
        'response_body_lengkap' => $debugData['response_body'] ?? $result['data'],
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 2: Ambil Data Mahasiswa
// ============================================================
Route::get('/langkah/2-mahasiswa', function (UnujaSsoService $sso) {
    $result = $sso->getMahasiswa();
    return response()->json([
        'langkah' => '2. Ambil Data Mahasiswa',
        'body_dikirim' => ['filter' => 'mahasiswa', 'pagination' => 'off'],
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 3: Cari Mahasiswa
// Buka: http://chatbot-.test/langkah/3-cari-mahasiswa?q=Ahmad
// ============================================================
Route::get('/langkah/3-cari-mahasiswa', function (Request $request, UnujaSsoService $sso) {
    $query = $request->query('q', '');
    $result = $sso->fetchPortalData('mahasiswa', [
        'search' => $query,
        'pagination' => 'on',
    ]);
    return response()->json([
        'langkah' => '3. Cari Mahasiswa',
        'pencarian' => $query,
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 4: Tahun Ajaran
// ============================================================
Route::get('/langkah/4-tahun-ajaran', function (UnujaSsoService $sso) {
    $result = $sso->getTahunAjaran();
    return response()->json([
        'langkah' => '4. Ambil Data Tahun Ajaran',
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 5: Transkrip
// Buka: http://chatbot-.test/langkah/5-transkrip?nim=XXXX
// ============================================================
Route::get('/langkah/5-transkrip', function (Request $request, UnujaSsoService $sso) {
    $nim = $request->query('nim', '');
    if (empty($nim)) {
        return response()->json([
            'langkah' => '5. Transkrip Nilai',
            'pesan' => 'Tambahkan parameter NIM! Contoh: /langkah/5-transkrip?nim=2021010001',
        ]);
    }
    $result = $sso->getTranskrip($nim);
    return response()->json([
        'langkah' => '5. Transkrip Nilai',
        'nim' => $nim,
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 6: Jadwal Kuliah
// ============================================================
Route::get('/langkah/6-jadwal', function (UnujaSsoService $sso) {
    $result = $sso->getJadwal();
    return response()->json([
        'langkah' => '6. Jadwal Kuliah',
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// LANGKAH 7: Daftar Mata Kuliah
// ============================================================
Route::get('/langkah/7-matakuliah', function (UnujaSsoService $sso) {
    $result = $sso->getMatakuliah();
    return response()->json([
        'langkah' => '7. Daftar Mata Kuliah',
        'hasil' => $result,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

// ============================================================
// DASHBOARD ADMIN
// ============================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('mahasiswa/export', [\App\Http\Controllers\Admin\MahasiswaController::class, 'export'])->name('mahasiswa.export');
        Route::post('mahasiswa/sync', [\App\Http\Controllers\Admin\MahasiswaController::class, 'syncStep'])->name('mahasiswa.sync');
        Route::post('mahasiswa/enrich', [\App\Http\Controllers\Admin\MahasiswaController::class, 'enrich'])->name('mahasiswa.enrich');
        Route::get('mahasiswa', [\App\Http\Controllers\Admin\MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::get('mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'show'])->name('mahasiswa.show');
        Route::put('mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'update'])->name('mahasiswa.update');
        Route::delete('mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    });
});
