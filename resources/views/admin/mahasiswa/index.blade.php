@extends('layouts.admin')

@section('title', 'Data Mahasiswa')
@section('subtitle', 'Cari, filter, perbarui, dan sinkronkan data dari SSO')

@section('content')
    {{-- Panel sinkronisasi --}}
    <section class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-slate-900">Sinkronisasi dari SSO UNUJA</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($totalData, 0, ',', '.') }} mahasiswa tersimpan lokal.
                    Sinkronisasi menarik seluruh halaman data mahasiswa dari SSO secara bertahap.
                </p>
                <p id="sync-status" class="mt-2 text-xs text-slate-500">Siap disinkronkan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.mahasiswa.export', $filters) }}"
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Export CSV
                </a>
                <button type="button" id="sync-button"
                        class="rounded-xl bg-unuja-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-unuja-800 disabled:cursor-not-allowed disabled:opacity-60">
                    Sinkronkan Sekarang
                </button>
            </div>
        </div>

        <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
            <div id="sync-bar" class="h-2 w-0 rounded-full bg-unuja-600 transition-all duration-300"></div>
        </div>

        {{-- Lengkapi email asli --}}
        <div class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="font-semibold text-slate-900">Lengkapi Email Asli</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar SSO menyensor email (contoh <code class="rounded bg-slate-100 px-1 text-xs">ab******54@gmail.com</code>).
                    Endpoint transkrip menyimpan email asli, jadi data dilengkapi dari sana.
                </p>
                <p id="enrich-status" class="mt-2 text-xs text-slate-500">
                    @if ($emailTersensor > 0)
                        {{ number_format($emailTersensor, 0, ',', '.') }} email masih tersensor / kosong.
                    @else
                        Semua email sudah lengkap.
                    @endif
                </p>
            </div>

            <button type="button" id="enrich-button"
                    @disabled($emailTersensor === 0)
                    class="rounded-xl border border-unuja-600 px-4 py-2.5 text-sm font-semibold text-unuja-700 hover:bg-unuja-50 disabled:cursor-not-allowed disabled:opacity-50">
                Lengkapi Email
            </button>
        </div>

        <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
            <div id="enrich-bar" class="h-2 w-0 rounded-full bg-emerald-500 transition-all duration-300"></div>
        </div>
    </section>

    {{-- Filter --}}
    <section class="mt-6 rounded-2xl bg-white p-4 ring-1 ring-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-500">Cari</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="NIM, nama, email, prodi…"
                       class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Prodi</label>
                <select name="prodi" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600">
                    <option value="">Semua prodi</option>
                    @foreach ($prodiList as $prodi)
                        <option value="{{ $prodi }}" @selected(($filters['prodi'] ?? '') === $prodi)>{{ $prodi }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Angkatan</label>
                <select name="tahun_masuk" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600">
                    <option value="">Semua</option>
                    @foreach ($angkatanList as $angkatan)
                        <option value="{{ $angkatan }}" @selected(($filters['tahun_masuk'] ?? '') === (string) $angkatan)>
                            {{ substr((string) $angkatan, 0, 4) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    Terapkan
                </button>
                <a href="{{ route('admin.mahasiswa.index') }}"
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </form>
    </section>

    {{-- Tabel --}}
    <section class="mt-6 rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4 border-b border-slate-100">
            <p class="text-sm text-slate-600">
                Menampilkan <span class="font-semibold text-slate-900">{{ $mahasiswa->firstItem() ?? 0 }}–{{ $mahasiswa->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-slate-900">{{ number_format($mahasiswa->total(), 0, ',', '.') }}</span> mahasiswa
            </p>
            <a href="{{ route('admin.mahasiswa.index', array_merge($filters, ['sort' => ($filters['sort'] ?? '') === 'ipk' ? '' : 'ipk'])) }}"
               class="text-xs font-semibold text-unuja-700 hover:underline">
                {{ ($filters['sort'] ?? '') === 'ipk' ? 'Urutkan nama' : 'Urutkan IPK tertinggi' }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">NIM</th>
                        <th class="px-5 py-3 font-semibold">Nama</th>
                        <th class="px-5 py-3 font-semibold">Prodi</th>
                        <th class="px-5 py-3 font-semibold">Angkatan</th>
                        <th class="px-5 py-3 font-semibold">Semester</th>
                        <th class="px-5 py-3 font-semibold">IPK</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mahasiswa as $m)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $m->nim }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.mahasiswa.show', $m) }}" class="font-medium text-slate-800 hover:text-unuja-700">
                                    {{ $m->nama }}
                                </a>
                                <p class="text-xs text-slate-400">{{ $m->email ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $m->prodi ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $m->angkatan() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $m->semester ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="font-semibold {{ ($m->ipk ?? 0) >= 3 ? 'text-emerald-700' : 'text-slate-700' }}">
                                    {{ $m->ipk !== null ? number_format($m->ipk, 2) : '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $m->statusClasses() }}">
                                    {{ $m->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.mahasiswa.show', $m) }}"
                                   class="text-xs font-semibold text-unuja-700 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <p class="text-sm font-semibold text-slate-700">Belum ada data yang cocok</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($totalData === 0)
                                        Klik “Sinkronkan Sekarang” untuk menarik data dari SSO UNUJA.
                                    @else
                                        Coba ubah kata kunci atau filter.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mahasiswa->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $mahasiswa->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        const button = document.getElementById('sync-button');
        const bar = document.getElementById('sync-bar');
        const status = document.getElementById('sync-status');
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const endpoint = @json(route('admin.mahasiswa.sync'));

        const enrichButton = document.getElementById('enrich-button');
        const enrichBar = document.getElementById('enrich-bar');
        const enrichStatus = document.getElementById('enrich-status');
        const enrichEndpoint = @json(route('admin.mahasiswa.enrich'));
        const totalTersensor = {{ (int) $emailTersensor }};

        let running = false;

        async function syncAll() {
            if (running) return;
            running = true;
            button.disabled = true;
            button.textContent = 'Menyinkronkan…';
            bar.style.width = '0%';

            let page = 1;
            let lastPage = 1;
            let diproses = 0;
            let totalServer = null;

            try {
                do {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ page }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Sinkronisasi gagal.');
                    }

                    diproses += data.imported;
                    lastPage = data.last_page;
                    totalServer = data.total ?? totalServer;

                    const percent = Math.min(100, Math.round((data.page / lastPage) * 100));
                    bar.style.width = percent + '%';
                    status.textContent = `Halaman ${data.page} dari ${lastPage} — ${diproses} data diproses` +
                        (totalServer ? ` (total ${totalServer})` : '');

                    page = data.page + 1;
                } while (page <= lastPage);

                bar.style.width = '100%';
                status.textContent = `Selesai. ${diproses} data mahasiswa disinkronkan. Memuat ulang…`;
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                status.textContent = 'Gagal: ' + error.message;
                button.disabled = false;
                button.textContent = 'Coba Lagi';
            } finally {
                running = false;
            }
        }

        button.addEventListener('click', syncAll);

        async function enrichAll() {
            if (!enrichButton || enrichButton.disabled) return;
            enrichButton.disabled = true;
            enrichButton.textContent = 'Melengkapi…';
            enrichBar.style.width = '0%';

            let afterId = 0;
            let diproses = 0;
            let diperbarui = 0;

            try {
                while (true) {
                    const response = await fetch(enrichEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ after_id: afterId }),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Gagal melengkapi email.');
                    }

                    afterId = data.last_id;
                    diproses += data.processed;
                    diperbarui += data.updated;

                    const percent = totalTersensor > 0
                        ? Math.min(100, Math.round((diproses / totalTersensor) * 100))
                        : 100;
                    enrichBar.style.width = percent + '%';
                    enrichStatus.textContent = `${diproses} diperiksa, ${diperbarui} email dilengkapi…`;

                    if (data.done) break;
                }

                enrichBar.style.width = '100%';
                enrichStatus.textContent = `Selesai. ${diperbarui} email dilengkapi dari ${diproses} data. Memuat ulang…`;
                setTimeout(() => window.location.reload(), 900);
            } catch (error) {
                enrichStatus.textContent = 'Gagal: ' + error.message;
                enrichButton.disabled = false;
                enrichButton.textContent = 'Coba Lagi';
            }
        }

        if (enrichButton) {
            enrichButton.addEventListener('click', enrichAll);

            @if (request('enrich'))
                enrichAll();
            @endif
        }

        @if (request('sync'))
            syncAll();
        @endif
    })();
</script>
@endpush
