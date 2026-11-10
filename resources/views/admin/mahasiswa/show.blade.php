@extends('layouts.admin')

@section('title', 'Detail Mahasiswa')
@section('subtitle', $mhs->nama . ' — ' . $mhs->nim)

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.mahasiswa.index') }}" class="text-sm font-semibold text-unuja-700 hover:underline">← Kembali ke data mahasiswa</a>
    </div>

    @if ($masihTersensor)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
            </svg>
            <div>
                <p class="font-semibold">Email belum lengkap</p>
                <p class="mt-0.5">
                    Email mahasiswa ini kosong atau tersamar dari SSO. Gunakan tombol
                    <span class="font-semibold">Lengkapi Email</span> di halaman daftar untuk mengambil email asli dari endpoint transkrip.
                    Field tempat/tanggal lahir dan no. HP tetap tersensor oleh kebijakan server SSO.
                </p>
            </div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Profil --}}
        <div class="lg:col-span-2 space-y-6">
            <section class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="h-14 w-14 rounded-2xl bg-unuja-700 text-white flex items-center justify-center text-xl font-bold">
                            {{ strtoupper(substr($mhs->nama, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ $mhs->nama }}</h2>
                            <p class="mt-0.5 font-mono text-sm text-slate-500">{{ $mhs->nim }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $mhs->prodi ?? '-' }} — {{ $mhs->fakultas ?? '-' }}</p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $mhs->statusClasses() }}">
                        {{ $mhs->statusLabel() }}
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">IPK</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $mhs->ipk !== null ? number_format($mhs->ipk, 2) : '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">SKS Lulus</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $mhs->sks_ketuntasan ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">Semester</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $mhs->semester ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">Angkatan</p>
                        <p class="mt-1 text-lg font-bold text-slate-900">{{ $mhs->angkatan() }}</p>
                    </div>
                </div>

                <dl class="mt-6 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                    @php
                        $details = [
                            'Jenis kelamin' => $mhs->jenis_kelamin === 'L' ? 'Laki-laki' : ($mhs->jenis_kelamin === 'P' ? 'Perempuan' : null),
                            'Email' => $mhs->email,
                            'No. HP' => $mhs->no_hp,
                            'Telegram ID' => $mhs->telegram_id,
                            'Tempat lahir' => $mhs->tempat_lahir,
                            'Tanggal lahir' => $mhs->tanggal_lahir?->translatedFormat('d F Y'),
                            'Tahun masuk' => $mhs->tahun_masuk,
                            'Tahun keluar' => $mhs->tahun_keluar,
                            'Dosen wali' => $mhs->wali,
                            'NIDN wali' => $mhs->nidn,
                            'Kurikulum' => $mhs->id_kurikulum,
                            'ID prodi (id_sms)' => $mhs->id_sms,
                            'Sinkron terakhir' => $mhs->synced_at?->translatedFormat('d F Y, H:i') . ' WIB',
                        ];
                    @endphp

                    @foreach ($details as $label => $value)
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-800">{{ $value ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Form edit --}}
            <section class="rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                <h3 class="font-semibold text-slate-900">Perbarui Data</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Nama dan kontak berasal dari SSO dan akan ditimpa pada sinkronisasi berikutnya.
                    Hanya <span class="font-semibold">status</span> dan <span class="font-semibold">catatan</span> yang tersimpan permanen sebagai data lokal.
                </p>

                <form method="POST" action="{{ route('admin.mahasiswa.update', $mhs) }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Nama lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $mhs->nama) }}" required
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Email</label>
                        <input type="email" name="email" value="{{ old('email', $mhs->email) }}"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">No. HP</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp', $mhs->no_hp) }}"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Telegram ID</label>
                        <input type="text" name="telegram_id" value="{{ old('telegram_id', $mhs->telegram_id) }}"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $mhs->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-500">Catatan admin</label>
                        <textarea name="catatan" rows="3"
                                  class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20"
                                  placeholder="Contoh: sudah menghubungi terkait KRS…">{{ old('catatan', $mhs->catatan) }}</textarea>
                    </div>

                    <div class="sm:col-span-2 flex items-center justify-end">
                        <button type="submit" class="rounded-xl bg-unuja-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-unuja-800">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- Aksi --}}
        <aside class="space-y-6">
            <section class="rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm">
                <h3 class="font-semibold text-slate-900">Informasi Data</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li class="flex justify-between gap-3">
                        <span>ID internal</span>
                        <span class="font-medium text-slate-800">#{{ $mhs->id }}</span>
                    </li>
                    <li class="flex justify-between gap-3">
                        <span>Dibuat</span>
                        <span class="font-medium text-slate-800">{{ $mhs->created_at?->translatedFormat('d M Y') }}</span>
                    </li>
                    <li class="flex justify-between gap-3">
                        <span>Diperbarui</span>
                        <span class="font-medium text-slate-800">{{ $mhs->updated_at?->translatedFormat('d M Y') }}</span>
                    </li>
                    <li class="flex justify-between gap-3">
                        <span>ID fakultas</span>
                        <span class="font-medium text-slate-800">{{ $mhs->id_fakultas ?? '—' }}</span>
                    </li>
                </ul>
            </section>

            <section class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <h3 class="font-semibold text-rose-800">Hapus dari dashboard</h3>
                <p class="mt-1 text-sm text-rose-700">
                    Data hanya dihapus dari database lokal. Sinkronisasi berikutnya akan memunculkannya kembali.
                </p>

                <form method="POST" action="{{ route('admin.mahasiswa.destroy', $mhs) }}" class="mt-4"
                      onsubmit="return confirm('Hapus {{ $mhs->nama }} dari dashboard?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
                        Hapus Data Lokal
                    </button>
                </form>
            </section>
        </aside>
    </div>
@endsection
