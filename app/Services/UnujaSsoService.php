<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnujaSsoService
{
    protected string $baseUrl = 'https://sso.unuja.ac.id';
    protected ?string $appId;
    protected ?string $devId;
    protected ?string $xToken;

    public const PRODI_TE = '0f6bb17f-efdd-493c-8939-30c215bd9fb1';      // Teknik Elektro
    public const PRODI_IF = '1319bfef-2188-4240-b7a3-7a507bec2e64';      // Teknik Informatika
    public const PRODI_TI = '6bb7c485-08a3-465c-b549-e5223c2affe2';      // Teknologi Informasi
    public const PRODI_TIP = '6dc3cb7a-fc03-4c1a-a66f-faa4de36d6e8';     // Teknik Industri Pertanian

    // Alias lama agar kode yang sudah ada tidak rusak.
    public const PRODI_ELEKTRO = self::PRODI_TE;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.unuja.base_url', 'https://sso.unuja.ac.id'), '/');
        $this->appId = config('services.unuja.app_id');
        $this->devId = config('services.unuja.dev_id');
        $this->xToken = config('services.unuja.x_token');
    }

    public function headers(): array
    {
        return [
            'X-Token' => $this->xToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'Naja-UNUJA-AI/1.0',
        ];
    }

    /**
     * Otorisasi dan simpan respons LENGKAP ke file untuk debugging
     */
    public function authorize(): array
    {
        $authUrl = $this->baseUrl . '/portal/data/authorize';

        try {
            $response = Http::withoutVerifying()
                ->timeout(12)
                ->withHeaders($this->headers())
                ->post($authUrl, [
                    'app_id' => $this->appId,
                    'dev_id' => $this->devId,
                ]);

            $rawBody = $response->body();
            $json = $response->json();
            $responseHeaders = $response->headers();

            // Simpan respons LENGKAP ke file (body + headers) untuk debugging
            $debugData = [
                'response_headers' => $responseHeaders,
                'response_body' => $json,
                'raw_body' => $rawBody,
            ];
            file_put_contents(
                storage_path('app/sso_debug_authorize.json'),
                json_encode($debugData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

            // Cari token dengan berbagai strategi
            $token = $this->extractToken($json);

            // Jika belum ketemu, coba cari di response header
            if (!$token) {
                foreach ($responseHeaders as $headerName => $headerValues) {
                    $headerLower = strtolower($headerName);
                    if (str_contains($headerLower, 'token') || str_contains($headerLower, 'authorization')) {
                        $val = is_array($headerValues) ? ($headerValues[0] ?? '') : $headerValues;
                        $val = trim(str_replace('Bearer ', '', $val));
                        if (strlen($val) === 32) {
                            $token = $val;
                            break;
                        }
                    }
                }
            }

            if ($token) {
                Cache::put('sso_unuja_access_token', $token, now()->addHours(5));
            }

            // Simpan seluruh info otorisasi (urls, scope, filters, header token)
            // supaya tidak perlu authorize ulang di setiap request.
            $info = $json['data']['info'] ?? null;
            if (is_array($info) && $response->successful()) {
                Cache::put('sso_unuja_auth', $info, now()->addHours(5));
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'token_extracted' => $token,
                'info' => $info,
                'data' => $json ?? $rawBody,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Ekstrak token dari respons authorize.
     * Cari string 32 karakter alfanumerik di semua level.
     */
    public function extractToken(mixed $data): ?string
    {
        if (!is_array($data)) {
            return null;
        }

        // 0. Sumber utama: URL data pada info.urls.data yang berakhiran token.
        //    Contoh: https://sso.unuja.ac.id/portal/data/data/EF642WmNzl0LblnhyGhcLa7N48gx8UxU
        $dataUrl = $data['data']['info']['urls']['data'] ?? $data['info']['urls']['data'] ?? null;
        if (is_string($dataUrl) && preg_match('~/data/([A-Za-z0-9]{16,64})$~', $dataUrl, $m)) {
            return $m[1];
        }

        // 1. Cek key populer secara eksplisit
        $tokenKeys = ['token', 'access_token', 'api_token', 'auth_token', 'data_token'];
        foreach ($tokenKeys as $key) {
            // Level 0
            if (isset($data[$key]) && is_string($data[$key]) && strlen($data[$key]) >= 20) {
                return $data[$key];
            }
            // Level 1 (data.token)
            if (isset($data['data'][$key]) && is_string($data['data'][$key]) && strlen($data['data'][$key]) >= 20) {
                return $data['data'][$key];
            }
            // Level 2 (data.data.token)
            if (isset($data['data']['data'][$key]) && is_string($data['data']['data'][$key]) && strlen($data['data']['data'][$key]) >= 20) {
                return $data['data']['data'][$key];
            }
            // Level 3 (data.data.info.token)
            if (isset($data['data']['data']['info'][$key]) && is_string($data['data']['data']['info'][$key]) && strlen($data['data']['data']['info'][$key]) >= 20) {
                return $data['data']['data']['info'][$key];
            }
        }

        // 2. Cari secara rekursif: string ≥20 karakter yang key-nya mengandung "token"
        return $this->findTokenRecursive($data);
    }

    /**
     * Pencarian rekursif token di seluruh struktur JSON.
     */
    protected function findTokenRecursive(array $data): ?string
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && strlen($value) >= 20 && strlen($value) <= 64) {
                $keyLower = strtolower((string) $key);
                if (str_contains($keyLower, 'token') || $keyLower === 'hash' || $keyLower === 'key') {
                    return $value;
                }
            }
            if (is_array($value)) {
                $found = $this->findTokenRecursive($value);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    /**
     * Mendapatkan Access Token (32 karakter) dari /portal/data/authorize
     */
    public function getAccessToken(bool $forceRefresh = false): ?string
    {
        if (!$forceRefresh) {
            $cached = Cache::get('sso_unuja_access_token');
            if ($cached) {
                return $cached;
            }
        }

        $result = $this->authorize();
        return $result['token_extracted'] ?? null;
    }

    /**
     * Mengambil info otorisasi (urls, scope, filters) yang tersimpan di cache.
     * Melakukan authorize otomatis bila cache kosong/kedaluwarsa.
     */
    public function getAuthInfo(bool $forceRefresh = false): ?array
    {
        if (!$forceRefresh) {
            $cached = Cache::get('sso_unuja_auth');
            if (is_array($cached) && !empty($cached['urls']['data'])) {
                return $cached;
            }
        }

        $result = $this->authorize();

        return $result['info'] ?? null;
    }

    /**
     * URL endpoint data lengkap (sudah berisi token) dari hasil authorize.
     * Contoh: https://sso.unuja.ac.id/portal/data/data/{token}
     */
    public function getDataUrl(bool $forceRefresh = false): ?string
    {
        $info = $this->getAuthInfo($forceRefresh);

        return $info['urls']['data'] ?? null;
    }

    /**
     * Daftar URL endpoint lain (agenda, calendar-event, telegram, public, dll).
     */
    public function getUrls(bool $forceRefresh = false): array
    {
        return $this->getAuthInfo($forceRefresh)['urls'] ?? [];
    }

    /**
     * Scope & hak akses yang diberikan oleh SSO.
     */
    public function getScope(bool $forceRefresh = false): array
    {
        $info = $this->getAuthInfo($forceRefresh);

        return [
            'hak_akses' => $info['hak_akses'] ?? [],
            'scope' => $info['scope'] ?? [],
            'filters' => $info['urls']['filters'] ?? [],
            'waktu_berlaku' => $info['waktu_berlaku'] ?? null,
        ];
    }

    /**
     * Eksekusi pemanggilan data terpusat:
     * POST {urls.data} hasil authorize, contoh https://sso.unuja.ac.id/portal/data/data/{token}
     *
     * @param  string  $filter  mahasiswa|fakultas|prodi|program_studi|dosen|lembaga|struktur|
     *                          karyawan|matakuliah|jadwal_matakuliah|mahasiswa_matakuliah|
     *                          dosen_matakuliah|tahun_ajaran|kurikulum|transkrip|alumni
     */
    public function fetchPortalData(string $filter, array $body = [], bool $retry = true): array
    {
        $url = $this->getDataUrl();

        if (!$url) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Gagal mendapatkan token otorisasi dari SSO UNUJA.',
            ];
        }

        $payload = array_merge([
            'filter' => $filter,
            'pagination' => 'off',
        ], $body);

        try {
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders($this->headers())
                ->post($url, $payload);

            if (in_array($response->status(), [401, 403], true) && $retry) {
                Cache::forget('sso_unuja_access_token');
                Cache::forget('sso_unuja_auth');

                return $this->fetchPortalData($filter, $body, false);
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'url' => $url,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'url' => $url,
                'message' => $response->json('message') ?? ('Server kampus merespons status ' . $response->status()),
                'raw' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'url' => $url,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getTranskrip(string $nim): array
    {
        return $this->fetchPortalData('transkrip', ['nim' => $nim]);
    }

    /**
     * Identitas mahasiswa dari endpoint transkrip.
     * Endpoint ini mengembalikan email TANPA sensor (berbeda dari filter mahasiswa).
     */
    public function getIdentitasTranskrip(string $nim): ?array
    {
        $result = $this->getTranskrip($nim);

        if (! ($result['success'] ?? false)) {
            return null;
        }

        return $result['data']['data']['mahasiswa'] ?? null;
    }

    /**
     * Ambil identitas banyak NIM sekaligus (paralel, 10 per gelombang).
     *
     * @param  array<int, string>  $nims
     * @return array<string, array>  nim => identitas
     */
    public function getIdentitasTranskripBatch(array $nims): array
    {
        $nims = array_values(array_unique(array_filter($nims)));
        $url = $this->getDataUrl();

        if (! $url || $nims === []) {
            return [];
        }

        $hasil = [];

        foreach (array_chunk($nims, 10) as $chunk) {
            try {
                $responses = \Illuminate\Support\Facades\Http::pool(function ($pool) use ($chunk, $url) {
                    foreach ($chunk as $index => $nim) {
                        $pool->as((string) $index)
                            ->withoutVerifying()
                            ->timeout(30)
                            ->withHeaders($this->headers())
                            ->post($url, ['filter' => 'transkrip', 'nim' => $nim]);
                    }
                });
            } catch (\Throwable $e) {
                continue;
            }

            foreach ($chunk as $index => $nim) {
                $response = $responses[(string) $index] ?? null;

                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $identitas = $response->json('data.mahasiswa');

                    if (is_array($identitas)) {
                        $hasil[$nim] = $identitas;
                    }
                }
            }
        }

        return $hasil;
    }

    public function getJadwal(?string $idSms = null, ?string $idSmt = null): array
    {
        return $this->fetchPortalData('jadwal_matakuliah', [
            'id_sms' => $idSms ?? self::PRODI_IF,
            'id_smt' => $idSmt ?? $this->getSemesterAktif(),
        ]);
    }

    /**
     * Jadwal kuliah yang benar-benar diambil oleh seorang mahasiswa:
     * KRS (id_kelas) -> jadwal_matakuliah prodi -> irisan kelas.
     */
    public function getJadwalMahasiswa(string $nim, ?string $idSmt = null): array
    {
        $idSmt = $idSmt ?? $this->getSemesterAktif();

        $mahasiswa = $this->getMahasiswaByNim($nim);
        if (!$mahasiswa) {
            return [
                'success' => false,
                'status' => 404,
                'message' => "Mahasiswa dengan NIM {$nim} tidak ditemukan.",
            ];
        }

        $krs = $this->getKrs($nim, null, $idSmt);
        $kelasIds = collect($krs['data']['data'] ?? [])
            ->pluck('id_kelas')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($kelasIds)) {
            return [
                'success' => true,
                'status' => 200,
                'message' => "Belum ada mata kuliah yang diambil pada semester {$idSmt}.",
                'mahasiswa' => $mahasiswa,
                'id_smt' => $idSmt,
                'data' => ['data' => []],
            ];
        }

        $jadwal = $this->fetchPortalData('jadwal_matakuliah', [
            'id_sms' => $mahasiswa['id_sms'] ?? null,
            'id_smt' => $idSmt,
        ]);

        $rows = collect($jadwal['data']['data'] ?? [])
            ->filter(fn ($row) => in_array((string) ($row['id_kelas'] ?? ''), $kelasIds, true))
            ->sortBy(fn ($row) => $this->hariKe($row['nama_hari'] ?? '') . ' ' . ($row['nama_jam'] ?? ''))
            ->values()
            ->all();

        return [
            'success' => $jadwal['success'] ?? true,
            'status' => 200,
            'message' => "Jadwal kuliah NIM {$nim} semester {$idSmt}.",
            'mahasiswa' => $mahasiswa,
            'id_smt' => $idSmt,
            'id_kelas' => $kelasIds,
            'data' => ['data' => $rows],
        ];
    }

    /**
     * Cari satu mahasiswa berdasarkan NIM (pencocokan persis).
     */
    public function getMahasiswaByNim(string $nim): ?array
    {
        $result = $this->fetchPortalData('mahasiswa', ['search' => $nim]);

        foreach ($result['data']['data'] ?? [] as $row) {
            if ((string) ($row['nim'] ?? '') === $nim) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Urutan hari untuk pengurutan jadwal.
     */
    protected function hariKe(string $hari): int
    {
        $urutan = [
            'senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4,
            'jumat' => 5, 'sabtu' => 6, 'ahad' => 7, 'minggu' => 7,
        ];

        return $urutan[strtolower(trim($hari))] ?? 99;
    }

    public function getKrs(string $nim, ?string $idSms = null, ?string $idSmt = null): array
    {
        $body = [
            'nim' => $nim,
            'id_smt' => $idSmt ?? $this->getSemesterAktif(),
        ];

        // id_sms bersifat filter: jangan dipaksa ke satu prodi, karena akan
        // mengosongkan hasil untuk mahasiswa prodi lain.
        if ($idSms) {
            $body['id_sms'] = $idSms;
        }

        return $this->fetchPortalData('mahasiswa_matakuliah', $body);
    }

    /**
     * ID semester yang sedang aktif (kolom aktif = 'y' pada filter tahun_ajaran).
     */
    public function getSemesterAktif(): ?string
    {
        $cached = Cache::get('sso_unuja_semester_aktif');
        if ($cached) {
            return $cached;
        }

        $result = $this->fetchPortalData('tahun_ajaran');
        foreach ($result['data']['data'] ?? [] as $row) {
            if (($row['aktif'] ?? null) === 'y' && !empty($row['id_smt'])) {
                Cache::put('sso_unuja_semester_aktif', $row['id_smt'], now()->addDay());

                return $row['id_smt'];
            }
        }

        return null;
    }

    public function getMahasiswa(string $search = ''): array
    {
        $body = [];
        if (!empty($search)) {
            $body['search'] = $search;
        }
        return $this->fetchPortalData('mahasiswa', $body);
    }

    public function getTahunAjaran(): array
    {
        return $this->fetchPortalData('tahun_ajaran');
    }

    public function getMatakuliah(?string $idSms = null): array
    {
        return $this->fetchPortalData('matakuliah', ['id_sms' => $idSms ?? self::PRODI_IF]);
    }

    public function getFakultas(): array
    {
        return $this->fetchPortalData('fakultas');
    }

    /**
     * @param  int|string|null  $idFakultas  Default 2 (Fakultas Teknik).
     */
    public function getProdi(int|string|null $idFakultas = 2): array
    {
        return $this->fetchPortalData('prodi', ['id_fakultas' => $idFakultas]);
    }

    public function getProgramStudi(int|string|null $idFakultas = 2): array
    {
        return $this->fetchPortalData('program_studi', ['id_fakultas' => $idFakultas]);
    }

    public function getDosen(): array
    {
        return $this->fetchPortalData('dosen');
    }

    public function getKaryawan(): array
    {
        return $this->fetchPortalData('karyawan');
    }

    public function getLembaga(): array
    {
        return $this->fetchPortalData('lembaga');
    }

    public function getStruktur(): array
    {
        return $this->fetchPortalData('struktur');
    }

    public function getAlumni(): array
    {
        return $this->fetchPortalData('alumni');
    }

    public function getKurikulum(?string $idSms = null): array
    {
        return $this->fetchPortalData('kurikulum', ['id_sms' => $idSms ?? self::PRODI_IF]);
    }

    public function getDosenMatakuliah(?string $idSms = null): array
    {
        return $this->fetchPortalData('dosen_matakuliah', ['id_sms' => $idSms ?? self::PRODI_IF]);
    }

    /**
     * Pagination aktif mengembalikan struktur paginator Laravel:
     * data.data = baris, data.current_page, data.total, data.per_page, data.last_page.
     */
    public function getMahasiswaPaginated(int $page = 1, ?string $search = null): array
    {
        $body = ['pagination' => 'on', 'page' => $page];
        if ($search !== null && $search !== '') {
            $body['search'] = $search;
        }

        return $this->fetchPortalData('mahasiswa', $body);
    }

    /**
     * Satu halaman data mahasiswa beserta metadata pagination.
     * Dipakai oleh dashboard admin untuk sinkronisasi bertahap.
     */
    public function fetchMahasiswaPage(int $page = 1): array
    {
        $result = $this->fetchPortalData('mahasiswa', [
            'pagination' => 'on',
            'page' => max(1, $page),
        ]);

        if (! ($result['success'] ?? false)) {
            return [
                'success' => false,
                'status' => $result['status'] ?? 500,
                'message' => $result['message'] ?? 'Gagal mengambil data mahasiswa dari SSO.',
                'rows' => [],
                'current_page' => $page,
                'last_page' => 1,
                'total' => 0,
            ];
        }

        $paginator = $result['data']['data'] ?? [];

        return [
            'success' => true,
            'status' => $result['status'] ?? 200,
            'rows' => $paginator['data'] ?? [],
            'current_page' => (int) ($paginator['current_page'] ?? $page),
            'last_page' => (int) ($paginator['last_page'] ?? 1),
            'per_page' => (int) ($paginator['per_page'] ?? 10),
            'total' => (int) ($paginator['total'] ?? 0),
        ];
    }

    /**
     * Endpoint publik tanpa token: jadwal shalat.
     */
    public function getJadwalShalat(): array
    {
        return $this->fetchPublic('jadwal-shalat');
    }

    /**
     * Endpoint publik tanpa token: tanggal hijriah.
     */
    public function getTanggalHijriah(): array
    {
        return $this->fetchPublic('tanggal-hijriah');
    }

    protected function fetchPublic(string $path): array
    {
        $url = $this->baseUrl . '/portal/data/' . ltrim($path, '/');

        try {
            $response = Http::withoutVerifying()->timeout(15)->get($url);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'url' => $url,
                'data' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'url' => $url,
                'message' => $e->getMessage(),
            ];
        }
    }
}
