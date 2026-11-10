<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\UnujaSsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MahasiswaController extends Controller
{
    public function __construct(private readonly UnujaSsoService $sso)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'prodi', 'fakultas', 'tahun_masuk', 'status', 'sort']);

        $mahasiswa = Mahasiswa::query()
            ->cari($filters['q'] ?? null)
            ->filter($filters)
            ->when(
                ($filters['sort'] ?? '') === 'ipk',
                fn ($q) => $q->orderByDesc('ipk'),
                fn ($q) => $q->orderBy('nama'),
            )
            ->paginate(15)
            ->withQueryString();

        return view('admin.mahasiswa.index', [
            'mahasiswa' => $mahasiswa,
            'filters' => $filters,
            'statuses' => Mahasiswa::STATUSES,
            'prodiList' => Mahasiswa::query()->whereNotNull('prodi')->distinct()->orderBy('prodi')->pluck('prodi'),
            'fakultasList' => Mahasiswa::query()->whereNotNull('fakultas')->distinct()->orderBy('fakultas')->pluck('fakultas'),
            'angkatanList' => Mahasiswa::query()
                ->whereNotNull('tahun_masuk')
                ->where('tahun_masuk', '!=', '')
                ->distinct()
                ->orderByDesc('tahun_masuk')
                ->limit(15)
                ->pluck('tahun_masuk'),
            'totalData' => Mahasiswa::count(),
            'emailTersensor' => $this->jumlahEmailTersensor(),
        ]);
    }

    public function show(Mahasiswa $mahasiswa): View
    {
        // Email dari filter "mahasiswa" disensor oleh SSO. Endpoint transkrip
        // mengembalikan email asli, jadi kita lengkapi otomatis saat dibuka.
        $this->lengkapiDataPribadi($mahasiswa);

        return view('admin.mahasiswa.show', [
            'mhs' => $mahasiswa->refresh(),
            'statuses' => Mahasiswa::STATUSES,
            'masihTersensor' => ! $this->nilaiTersedia($mahasiswa->email),
        ]);
    }

    /**
     * Lengkapi email/no_hp dari endpoint transkrip (data tidak disensor).
     */
    protected function lengkapiDataPribadi(Mahasiswa $mahasiswa): bool
    {
        if (! $this->perluDilengkapi($mahasiswa)) {
            return false;
        }

        $identitas = $this->sso->getIdentitasTranskrip($mahasiswa->nim);

        return $identitas ? $this->terapkanIdentitas($mahasiswa, $identitas) : false;
    }

    /**
     * Apakah masih ada field pribadi yang tersensor/kosong?
     */
    protected function perluDilengkapi(Mahasiswa $mahasiswa): bool
    {
        return $this->nilaiTersensor($mahasiswa->email);
    }

    /**
     * Terapkan hasil transkrip ke model. Hanya menulis bila ada perubahan.
     */
    protected function terapkanIdentitas(Mahasiswa $mahasiswa, array $identitas): bool
    {
        $updates = [];

        if (filled($identitas['email'] ?? null) && ! $this->nilaiTersensor($identitas['email'])) {
            $updates['email'] = $identitas['email'];
        }

        if (filled($identitas['no_hp'] ?? null) && ! $this->nilaiTersensor($identitas['no_hp'])) {
            $updates['no_hp'] = $identitas['no_hp'];
        }

        if ($updates === []) {
            return false;
        }

        $berubah = false;
        foreach ($updates as $field => $value) {
            if ((string) $mahasiswa->{$field} !== (string) $value) {
                $berubah = true;
            }
        }

        if (! $berubah) {
            return false;
        }

        $mahasiswa->update($updates);

        return true;
    }

    protected function nilaiTersensor(?string $value): bool
    {
        return is_string($value) && str_contains($value, '*');
    }

    protected function nilaiTersedia(?string $value): bool
    {
        return filled($value) && ! $this->nilaiTersensor($value);
    }

    /**
     * Lengkapi email asli secara massal (batch + paralel) dengan cursor id.
     * Hanya memproses email yang masih tersensor / kosong.
     */
    public function enrich(Request $request): JsonResponse
    {
        @set_time_limit(180);

        $afterId = max(0, (int) $request->input('after_id', 0));
        $batchSize = 30;

        $items = Mahasiswa::query()
            ->where('id', '>', $afterId)
            ->where(function ($q) {
                $q->where('email', 'like', '%*%')->orWhereNull('email');
            })
            ->orderBy('id')
            ->limit($batchSize)
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => true,
                'last_id' => $afterId,
                'processed' => 0,
                'updated' => 0,
                'done' => true,
            ]);
        }

        $identitas = $this->sso->getIdentitasTranskripBatch($items->pluck('nim')->all());

        $updated = 0;
        foreach ($items as $item) {
            $data = $identitas[$item->nim] ?? null;

            if ($data && $this->terapkanIdentitas($item, $data)) {
                $updated++;
            }
        }

        return response()->json([
            'success' => true,
            'last_id' => (int) $items->last()->id,
            'processed' => $items->count(),
            'updated' => $updated,
            'done' => $items->count() < $batchSize,
        ]);
    }

    /**
     * Jumlah email yang masih tersensor / kosong.
     */
    protected function jumlahEmailTersensor(): int
    {
        return Mahasiswa::query()
            ->where(function ($q) {
                $q->where('email', 'like', '%*%')->orWhereNull('email');
            })
            ->count();
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'telegram_id' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:' . implode(',', array_keys(Mahasiswa::STATUSES))],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $mahasiswa->update($validated);

        return redirect()
            ->route('admin.mahasiswa.show', $mahasiswa)
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $nama = $mahasiswa->nama;
        $mahasiswa->delete();

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', "Data mahasiswa {$nama} dihapus dari dashboard. Data akan muncul lagi bila disinkronkan ulang.");
    }

    /**
     * Sinkronisasi satu halaman data mahasiswa dari SSO.
     * Dipanggil berulang oleh JavaScript sampai halaman terakhir.
     */
    public function syncStep(Request $request): JsonResponse
    {
        @set_time_limit(120);

        $page = max(1, (int) $request->input('page', 1));
        $result = $this->sso->fetchMahasiswaPage($page);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengambil data dari SSO.',
                'page' => $page,
            ], 422);
        }

        $rows = $result['rows'] ?? [];
        $imported = 0;

        DB::transaction(function () use ($rows, &$imported) {
            foreach ($rows as $row) {
                $nim = trim((string) ($row['nim'] ?? ''));

                if ($nim === '') {
                    continue;
                }

                $data = $this->mapFromSso($row);
                $existing = Mahasiswa::where('nim', $nim)->first();

                // Jangan timpa data lengkap yang sudah didapat dari transkrip
                // dengan versi tersensor dari filter mahasiswa.
                if ($existing) {
                    foreach (['email', 'no_hp', 'telegram_id', 'tempat_lahir', 'tanggal_lahir'] as $field) {
                        $masuk = $data[$field] ?? null;
                        $masuk = $masuk instanceof \DateTimeInterface ? $masuk->format('Y-m-d') : $masuk;

                        $lama = $existing->{$field};
                        $lama = $lama instanceof \DateTimeInterface ? $lama->format('Y-m-d') : $lama;

                        if (! $this->nilaiTersedia($masuk) && $this->nilaiTersedia($lama)) {
                            $data[$field] = $existing->{$field};
                        }
                    }
                }

                Mahasiswa::updateOrCreate(['nim' => $nim], $data);
                $imported++;
            }
        });

        $lastPage = (int) ($result['last_page'] ?? 1);

        return response()->json([
            'success' => true,
            'page' => (int) ($result['current_page'] ?? $page),
            'last_page' => $lastPage,
            'per_page' => $result['per_page'] ?? null,
            'total' => $result['total'] ?? null,
            'imported' => $imported,
            'done' => $page >= $lastPage,
        ]);
    }

    /**
     * Hanya kolom SSO yang ditimpa. status & catatan milik admin dibiarkan.
     */
    protected function mapFromSso(array $row): array
    {
        $tanggalLahir = $row['tanggal_lahir'] ?? null;
        if (! is_string($tanggalLahir) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) {
            $tanggalLahir = null;
        }

        return [
            'nama' => trim((string) ($row['nama'] ?? '-')),
            'jenis_kelamin' => $this->oneChar($row['jenis_kelamin'] ?? null),
            'email' => $row['email'] ?? null,
            'no_hp' => $row['no_hp'] ?: null,
            'telegram_id' => $row['telegram_id'] ?: null,
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $tanggalLahir,
            'tahun_masuk' => $row['tahun_masuk'] ?? null,
            'tahun_keluar' => $row['tahun_keluar'] ?? null,
            'id_fakultas' => isset($row['id_fakultas']) ? (string) $row['id_fakultas'] : null,
            'fakultas' => $row['fakultas'] ?? null,
            'id_sms' => $row['id_sms'] ?? null,
            'prodi' => $row['prodi'] ?? null,
            'id_kurikulum' => $row['id_kurikulum'] ?? null,
            'id_wali' => $row['id_wali'] ?? null,
            'wali' => $row['wali'] ?? null,
            'nidn' => $row['nidn'] ?? null,
            'ipk' => is_numeric($row['ipk'] ?? null) ? $row['ipk'] : null,
            'sks_ketuntasan' => is_numeric($row['sks_ketuntasan'] ?? null) ? (int) $row['sks_ketuntasan'] : null,
            'semester' => is_numeric($row['semester'] ?? null) ? (int) $row['semester'] : null,
            'synced_at' => now(),
        ];
    }

    protected function oneChar(mixed $value): ?string
    {
        $value = strtoupper(substr(trim((string) $value), 0, 1));

        return in_array($value, ['L', 'P'], true) ? $value : null;
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['q', 'prodi', 'fakultas', 'tahun_masuk', 'status']);

        $query = Mahasiswa::query()
            ->cari($filters['q'] ?? null)
            ->filter($filters)
            ->orderBy('nama');

        $filename = 'data-mahasiswa-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'NIM', 'Nama', 'L/P', 'Email', 'No HP', 'Fakultas', 'Prodi',
                'Angkatan', 'Semester', 'IPK', 'SKS Lulus', 'Wali', 'Status', 'Catatan',
            ]);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $m) {
                    fputcsv($out, [
                        $m->nim,
                        $m->nama,
                        $m->jenis_kelamin,
                        $m->email,
                        $m->no_hp,
                        $m->fakultas,
                        $m->prodi,
                        $m->angkatan(),
                        $m->semester,
                        $m->ipk,
                        $m->sks_ketuntasan,
                        $m->wali,
                        $m->statusLabel(),
                        $m->catatan,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
