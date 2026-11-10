@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan data mahasiswa dari SSO UNUJA')

@section('content')
    @php
        $cards = [
            ['label' => 'Total Mahasiswa', 'value' => number_format($total, 0, ',', '.'), 'tone' => 'bg-unuja-700 text-white', 'sub' => 'data tersimpan lokal'],
            ['label' => 'Status Aktif', 'value' => number_format($perStatus['aktif'] ?? 0, 0, ',', '.'), 'tone' => 'bg-white', 'sub' => 'mahasiswa aktif'],
            ['label' => 'Lulus', 'value' => number_format($perStatus['lulus'] ?? 0, 0, ',', '.'), 'tone' => 'bg-white', 'sub' => 'tercatat lulus'],
            ['label' => 'Cuti / Non-aktif', 'value' => number_format(($perStatus['cuti'] ?? 0) + ($perStatus['nonaktif'] ?? 0), 0, ',', '.'), 'tone' => 'bg-white', 'sub' => 'perlu perhatian'],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-2xl p-5 ring-1 ring-slate-200 shadow-sm {{ $card['tone'] }}">
                <p class="text-sm {{ str_contains($card['tone'], 'text-white') ? 'text-emerald-100' : 'text-slate-500' }}">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold {{ str_contains($card['tone'], 'text-white') ? 'text-white' : 'text-slate-900' }}">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs {{ str_contains($card['tone'], 'text-white') ? 'text-emerald-100/80' : 'text-slate-400' }}">{{ $card['sub'] }}</p>
            </div>
        @endforeach
    </div>

    @if ($total === 0)
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="text-lg font-semibold text-slate-800">Belum ada data mahasiswa</h2>
            <p class="mt-1 text-sm text-slate-500">Jalankan sinkronisasi dari API SSO untuk mulai mengelola data.</p>
            <a href="{{ route('admin.mahasiswa.index') }}"
               class="mt-5 inline-flex items-center gap-2 rounded-xl bg-unuja-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-unuja-800">
                Buka Data Mahasiswa &amp; Sinkronkan
            </a>
        </div>
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-5">
            {{-- Distribusi prodi --}}
            <section class="lg:col-span-3 rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-slate-900">Sebaran per Program Studi</h2>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="text-xs font-semibold text-unuja-700 hover:underline">Lihat data →</a>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($perProdi as $row)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $row->prodi }}</span>
                                <span class="text-slate-500">{{ number_format($row->jumlah, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1.5 h-2 w-full rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-unuja-600"
                                     style="width: {{ max(3, round(($row->jumlah / $maxProdi) * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach

                    @if ($perProdi->isEmpty())
                        <p class="text-sm text-slate-500">Data prodi belum tersedia.</p>
                    @endif
                </div>
            </section>

            {{-- Angkatan & sinkronisasi --}}
            <section class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
                    <h2 class="font-semibold text-slate-900">Angkatan Terbaru</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($perAngkatan as $row)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Angkatan {{ substr((string) $row->tahun_masuk, 0, 4) }}</span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                    {{ number_format($row->jumlah, 0, ',', '.') }}
                                </span>
                            </li>
                        @endforeach

                        @if ($perAngkatan->isEmpty())
                            <li class="text-sm text-slate-500">Belum ada data angkatan.</li>
                        @endif
                    </ul>
                </div>

                <div class="rounded-2xl bg-unuja-50 p-5 ring-1 ring-unuja-100">
                    <h2 class="font-semibold text-unuja-800">Sinkronisasi terakhir</h2>
                    <p class="mt-2 text-sm text-unuja-700">
                        {{ $lastSync ? \Illuminate\Support\Carbon::parse($lastSync)->translatedFormat('d F Y, H:i') . ' WIB' : 'Belum pernah disinkronkan' }}
                    </p>
                    <a href="{{ route('admin.mahasiswa.index', ['sync' => 1]) }}"
                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-unuja-700 px-4 py-2 text-sm font-semibold text-white hover:bg-unuja-800">
                        Sinkronkan sekarang
                    </a>
                </div>
            </section>
        </div>

        {{-- Data terbaru --}}
        <section class="mt-6 rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Data Terbaru</h2>
                <a href="{{ route('admin.mahasiswa.index') }}" class="text-xs font-semibold text-unuja-700 hover:underline">Semua mahasiswa →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">NIM</th>
                            <th class="px-5 py-3 font-semibold">Nama</th>
                            <th class="px-5 py-3 font-semibold">Prodi</th>
                            <th class="px-5 py-3 font-semibold">Angkatan</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($terbaru as $m)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $m->nim }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.mahasiswa.show', $m) }}" class="font-medium text-slate-800 hover:text-unuja-700">
                                        {{ $m->nama }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $m->prodi ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $m->angkatan() }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $m->statusClasses() }}">
                                        {{ $m->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
