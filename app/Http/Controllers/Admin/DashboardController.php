<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $total = Mahasiswa::count();

        $perStatus = Mahasiswa::query()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status => (int) $row->jumlah]);

        $perProdi = Mahasiswa::query()
            ->selectRaw('prodi, count(*) as jumlah')
            ->whereNotNull('prodi')
            ->groupBy('prodi')
            ->orderByDesc('jumlah')
            ->get();

        $perAngkatan = Mahasiswa::query()
            ->selectRaw('tahun_masuk, count(*) as jumlah')
            ->whereNotNull('tahun_masuk')
            ->where('tahun_masuk', '!=', '')
            ->groupBy('tahun_masuk')
            ->orderByDesc('tahun_masuk')
            ->limit(6)
            ->get();

        $terbaru = Mahasiswa::query()
            ->orderByDesc('synced_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $lastSync = Mahasiswa::max('synced_at');

        return view('admin.dashboard', [
            'total' => $total,
            'perStatus' => $perStatus,
            'perProdi' => $perProdi,
            'perAngkatan' => $perAngkatan,
            'terbaru' => $terbaru,
            'lastSync' => $lastSync,
            'statuses' => Mahasiswa::STATUSES,
            'maxProdi' => max(1, (int) ($perProdi->max('jumlah') ?? 1)),
        ]);
    }
}
