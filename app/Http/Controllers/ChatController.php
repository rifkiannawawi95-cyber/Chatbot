<?php

namespace App\Http\Controllers;

use App\Services\UnujaSsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected UnujaSsoService $sso;

    public function __construct(UnujaSsoService $sso)
    {
        $this->sso = $sso;
    }

    /**
     * Memproses percakapan dari mahasiswa dan memanggil API resmi SSO UNUJA.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = trim($request->input('message'));
        $lower = strtolower($userMessage);

        // 1. Ekstrak NIM jika ada (misal deretan angka 8-12 digit).
        //    NIM yang pernah dikirim diingat di session, karena percakapan
        //    chatbot bersifat stateless per request.
        $nim = $this->extractNim($userMessage);
        $nimDariSession = false;

        if ($nim) {
            session(['chat.nim' => $nim]);
        } else {
            // Utamakan NIM dari percakapan, lalu NIM hasil login Naja.
            $nim = session('chat.nim') ?: session('naja.nim');
            $nimDariSession = (bool) $nim;

            if ($nim && ! session()->has('chat.nim')) {
                session(['chat.nim' => $nim]);
            }
        }

        // 2. Bila user hanya membalas dengan NIM (mis. "2521300001" atau
        //    "nim saya 2521300001"), lanjutkan ke maksud yang sedang ditunggu.
        if ($nim && $this->isBareNim($userMessage, $nim)) {
            $pending = session('chat.pending');

            if (!in_array($pending, ['transkrip', 'krs', 'jadwal'], true)) {
                // Tidak ada konteks sebelumnya: tampilkan ringkasan profil + nilai.
                $pending = 'transkrip';
            }

            $lower .= ' ' . $pending;
            session()->forget('chat.pending');
        }

        // 3. Deteksi Sapaan Awal
        if ($this->isGreeting($lower)) {
            return response()->json([
                'reply' => "Halo! Saya Asisten Digital UNUJA. Ada yang bisa saya bantu terkait jadwal kuliah, KRS, tagihan keuangan, atau informasi akademik kampus hari ini? 😊",
                'type' => 'greeting',
            ]);
        }

        // 4. Batasan Pengetahuan (Tolak topik non-kampus secara halus)
        if ($this->isOutOfScope($lower)) {
            return response()->json([
                'reply' => "Wah, kalau topik itu di luar konteks layanan kampus UNUJA nih kak. Naja siap bantu seputar perkuliahan, nilai/transkrip, jadwal kuliah, KRS, dan informasi akademik UNUJA lainnya ya!",
                'type' => 'out_of_scope',
            ]);
        }

        // 5. Permintaan Transkrip / Nilai / IPK
        if (str_contains($lower, 'transkrip') || str_contains($lower, 'nilai') || str_contains($lower, 'ipk') || str_contains($lower, 'khs')) {
            if (!$nim) {
                session(['chat.pending' => 'transkrip']);

                return response()->json([
                    'reply' => "Untuk melihat data transkrip nilai langsung dari server resmi SSO UNUJA, boleh sebutkan NIM (Nomor Induk Mahasiswa) kamu kak? Contoh: *Transkrip NIM 2021010001*",
                    'type' => 'ask_nim',
                ]);
            }

            // Panggil API resmi: https://sso.unuja.ac.id/portal/data/transkrip
            $apiResult = $this->sso->getTranskrip($nim);

            if (!$apiResult['success']) {
                return response()->json([
                    'reply' => $this->handleApiError($apiResult['status'] ?? 500),
                    'type' => 'api_error',
                ]);
            }

            $data = $apiResult['data']['data'] ?? $apiResult['data'] ?? null;
            if (empty($data)) {
                return response()->json([
                    'reply' => "Data transkrip untuk NIM **{$nim}** belum ditemukan di server resmi SSO UNUJA kak. Pastikan NIM sudah benar ya!",
                    'type' => 'not_found',
                ]);
            }

            $reply = $this->formatTranskripResponse($nim, $data);
            return response()->json([
                'reply' => $reply,
                'type' => 'transkrip',
            ]);
        }

        // 6. Permintaan Jadwal Kuliah
        if (str_contains($lower, 'jadwal') || str_contains($lower, 'kuliah') || str_contains($lower, 'kelas')) {
            if (!$nim) {
                session(['chat.pending' => 'jadwal']);

                return response()->json([
                    'reply' => "Biar jadwal yang Naja tampilkan benar-benar jadwal kelas kamu (bukan jadwal semua prodi), boleh sebutkan NIM kamu kak? Contoh: *jadwal 2021010001*",
                    'type' => 'ask_nim',
                ]);
            }

            // Jadwal diambil dari KRS mahasiswa (id_kelas) lalu dicocokkan
            // dengan jadwal_matakuliah prodi pada semester aktif.
            $apiResult = $this->sso->getJadwalMahasiswa($nim);

            if (!$apiResult['success']) {
                if (($apiResult['status'] ?? 500) === 404) {
                    return response()->json([
                        'reply' => "NIM **{$nim}** tidak ditemukan di data mahasiswa UNUJA kak. Coba periksa kembali NIM-nya ya.",
                        'type' => 'not_found',
                    ]);
                }

                return response()->json([
                    'reply' => $this->handleApiError($apiResult['status'] ?? 500),
                    'type' => 'api_error',
                ]);
            }

            $data = $apiResult['data']['data'] ?? null;
            if (empty($data)) {
                return response()->json([
                    'reply' => "Belum ada jadwal kuliah untuk NIM **{$nim}** pada semester aktif ini kak. Kemungkinan KRS belum diisi/disetujui, atau memang belum ada kelas yang diambil.",
                    'type' => 'not_found',
                ]);
            }

            $reply = $this->formatJadwalResponse($data, $apiResult['mahasiswa'] ?? null, $apiResult['id_smt'] ?? null);
            return response()->json([
                'reply' => $reply,
                'type' => 'jadwal',
            ]);
        }

        // 7. Permintaan KRS (Mata Kuliah Mahasiswa)
        if (str_contains($lower, 'krs') || str_contains($lower, 'rencana studi')) {
            if (!$nim) {
                session(['chat.pending' => 'krs']);

                return response()->json([
                    'reply' => "Boleh infokan NIM kamu kak? Biar Naja cek status KRS kamu langsung ke server SSO UNUJA.",
                    'type' => 'ask_nim',
                ]);
            }

            // Panggil API resmi: https://sso.unuja.ac.id/portal/data/mahasiswa_matakuliah
            $apiResult = $this->sso->getKrs($nim);

            if (!$apiResult['success']) {
                return response()->json([
                    'reply' => $this->handleApiError($apiResult['status'] ?? 500),
                    'type' => 'api_error',
                ]);
            }

            $data = $apiResult['data']['data'] ?? $apiResult['data'] ?? null;
            if (empty($data)) {
                return response()->json([
                    'reply' => "Data KRS untuk NIM **{$nim}** belum ditemukan atau belum disetujui di portal akademik kak.",
                    'type' => 'not_found',
                ]);
            }

            $reply = $this->formatKrsResponse($nim, $data);
            return response()->json([
                'reply' => $reply,
                'type' => 'krs',
            ]);
        }

        // 8. Cek Tagihan UKT / Keuangan
        if (str_contains($lower, 'ukt') || str_contains($lower, 'tagihan') || str_contains($lower, 'biaya') || str_contains($lower, 'bayar') || str_contains($lower, 'keuangan')) {
            $nimInfo = $nim ? " untuk NIM **{$nim}**" : '';

            $reply = "Naja belum bisa menampilkan rincian tagihan UKT{$nimInfo} ya kak. "
                . "Data keuangan tidak termasuk dalam hak akses API SSO yang terhubung ke chatbot ini — "
                . "yang tersedia hanya data akademik (mahasiswa, prodi, KRS, jadwal, mata kuliah, dan nilai/transkrip).\n\n"
                . "Supaya angkanya akurat, silakan cek lewat jalur resmi berikut:\n"
                . "• Login ke portal SSO UNUJA, lalu buka menu **Keuangan / UKT**\n"
                . "• atau datang langsung ke **Biro Keuangan**, Gedung Rektorat Lt. 1 (Senin–Sabtu, 08.00–14.30 WIB)\n\n"
                . "Kalau butuh info akademik lain (jadwal, KRS, transkrip), Naja siap bantu ya kak! 😊";

            return response()->json([
                'reply' => $reply,
                'type' => 'financial',
            ]);
        }

        // 9. Kontak BAA & Biro Keuangan
        if (str_contains($lower, 'baa') || str_contains($lower, 'kontak') || str_contains($lower, 'layanan') || str_contains($lower, 'kantor')) {
            $reply = "Berikut informasi layanan kampus UNUJA:\n\n"
                . "• **Biro Administrasi Akademik (BAA)**\n"
                . "  Lokasi: Gedung Rektorat Lt. 1\n"
                . "  Jam Layanan: Senin – Sabtu, 08.00 – 14.30 WIB\n"
                . "  Email: baa@unuja.ac.id\n\n"
                . "• **Biro Keuangan UNUJA**\n"
                . "  Lokasi: Gedung Rektorat Lt. 1\n"
                . "  Layanan: Validasi UKT, beasiswa, dan dispensasi pembayaran.";

            return response()->json([
                'reply' => $reply,
                'type' => 'contact',
            ]);
        }

        // Fallback Umum Naja
        $nimTersimpan = session('chat.nim');

        return response()->json([
            'reply' => $nimTersimpan
                ? "NIM **{$nimTersimpan}** sudah Naja simpan kak. Mau cek **Transkrip Nilai**, **Jadwal Kuliah**, atau **Status KRS**? Cukup sebutkan salah satunya ya! 😊"
                : "Naja siap bantu kak! Mau cek **Transkrip Nilai**, **Jadwal Kuliah**, atau **Status KRS** hari ini? Cukup ketik pertanyaanmu beserta NIM kamu ya! 😊",
            'type' => 'general',
        ]);
    }

    /**
     * Penanganan pesan error API sesuai instruksi: bahasa manusia ramah.
     */
    protected function handleApiError(int $status): string
    {
        if ($status === 401) {
            return "Sesi login kamu sepertinya sudah habis kak. Yuk, login ulang ke SSO UNUJA agar saya bisa mengakses datamu.";
        }

        return "Mohon maaf kak, sepertinya jalur ke server kampus sedang gangguan atau datanya belum tersedia. Bisa dicoba lagi nanti ya!";
    }

    /**
     * Format penyajian transkrip nilai ke format poin / tabel rapi.
     */
    protected function formatTranskripResponse(string $nim, mixed $data): string
    {
        if (!is_array($data)) {
            return "Berikut data transkrip untuk NIM **{$nim}**:\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        }

        // Struktur resmi SSO: { mahasiswa, ketuntasan, matakuliah[] }
        if (isset($data['mahasiswa']) || isset($data['matakuliah'])) {
            $mhs = $data['mahasiswa'] ?? [];
            $ket = $data['ketuntasan'] ?? [];
            $mk = $data['matakuliah'] ?? [];

            $lines = ["Berikut ringkasan transkrip resmi dari SSO UNUJA:\n"];

            if (!empty($mhs)) {
                $lines[] = "• Nama: **" . ($mhs['nama'] ?? '-') . "**";
                $lines[] = "• NIM: **" . ($mhs['nim'] ?? $nim) . "**";
                $lines[] = "• Prodi: " . ($mhs['prodi'] ?? '-') . ' — ' . ($mhs['fakultas'] ?? '-');
            }

            if (!empty($ket)) {
                $lines[] = "• IPK: **" . ($ket['ipk_ketuntasan'] ?? '-') . "**";
                $lines[] = "• SKS lulus: " . ($ket['sks_ketuntasan'] ?? '-') . ' / ' . ($ket['jml_sks_lulus'] ?? '-');
                $lines[] = "• Kurikulum: " . ($ket['nama_kurikulum'] ?? '-');
            }

            $lines[] = "\nDaftar mata kuliah:";
            foreach (array_slice($mk, 0, 15) as $row) {
                $lines[] = "• **" . ($row['nama_matkul'] ?? '-') . '** (' . $this->trimSks($row['sks_mk'] ?? null) . ' SKS) — Nilai: **'
                    . ($row['n_jenjang_huruf'] ?? 'belum ada') . '**';
            }

            if (count($mk) > 15) {
                $lines[] = '*(dan ' . (count($mk) - 15) . ' mata kuliah lainnya)*';
            }

            $lines[] = "\nTranskrip lengkap bertanda tangan digital dapat kamu unduh melalui portal SSO UNUJA ya kak.";

            return implode("\n", $lines);
        }

        // Fallback: data berupa daftar baris.
        $items = isset($data[0]) ? $data : [$data];
        $totalItems = min(count($items), 15);

        $lines = ["Berikut ringkasan data transkrip resmi untuk NIM **{$nim}** dari SSO UNUJA:\n"];

        for ($i = 0; $i < $totalItems; $i++) {
            $row = $items[$i];
            $matkul = $row['matakuliah'] ?? $row['nama_matakuliah'] ?? $row['nama_matkul'] ?? $row['nama_mk'] ?? $row['mk'] ?? ('Mata Kuliah #' . ($i + 1));
            $sks = $this->trimSks($row['sks'] ?? $row['sks_mk'] ?? null);
            $nilai = $row['nilai'] ?? $row['nilai_huruf'] ?? $row['n_jenjang_huruf'] ?? $row['grade'] ?? '-';

            $lines[] = "• **{$matkul}** ({$sks} SKS) — Nilai: **{$nilai}**";
        }

        if (count($items) > 15) {
            $lines[] = "\n*(dan " . (count($items) - 15) . " mata kuliah lainnya)*";
        }

        $lines[] = "\nTranskrip lengkap bertanda tangan digital dapat kamu unduh langsung melalui portal SSO UNUJA ya kak.";

        return implode("\n", $lines);
    }

    /**
     * "3.0" -> "3", "3.5" -> "3.5"
     */
    protected function trimSks(mixed $sks): string
    {
        if ($sks === null || $sks === '') {
            return '-';
        }

        return rtrim(rtrim(number_format((float) $sks, 1, '.', ''), '0'), '.') ?: '0';
    }

    /**
     * Format penyajian jadwal perkuliahan.
     */
    protected function formatJadwalResponse(mixed $data, ?array $mahasiswa = null, ?string $idSmt = null): string
    {
        if (!is_array($data)) {
            return "Berikut data jadwal perkuliahan:\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        }

        $items = isset($data[0]) ? $data : [$data];

        $lines = ['Berikut jadwal kuliah yang benar-benar kamu ambil:'];

        if ($mahasiswa) {
            $lines[] = '• Nama: **' . ($mahasiswa['nama'] ?? '-') . '**';
            $lines[] = '• NIM: **' . ($mahasiswa['nim'] ?? '-') . '**';
            $lines[] = '• Prodi: ' . ($mahasiswa['prodi'] ?? '-');
        }

        $semester = $items[0]['nm_smt'] ?? $idSmt;
        if ($semester) {
            $lines[] = '• Semester: ' . $semester;
        }

        $lines[] = '';
        $currentDay = null;
        $no = 1;

        foreach (array_slice($items, 0, 30) as $row) {
            $hari = $row['nama_hari'] ?? 'Hari sesuai jadwal';

            if ($hari !== $currentDay) {
                $lines[] = "**{$hari}**";
                $currentDay = $hari;
            }

            $matkul = $row['nama_matkul'] ?? $row['matakuliah'] ?? ('Mata Kuliah #' . $no);
            $jam = $row['nama_jam'] ?? $row['jam'] ?? $row['waktu'] ?? '-';
            $ruang = $row['nama_ruangan'] ?? $row['ruang'] ?? $row['ruangan'] ?? '-';
            $dosen = $row['nama_dosen'] ?? $row['dosen'] ?? '-';
            $kelas = $row['nama_kelas'] ?? null;
            $sks = $row['sks_mk'] ?? null;

            $lines[] = "{$no}. **{$matkul}**" . ($kelas ? " (Kelas {$kelas})" : '')
                . ($sks ? ' — ' . $this->trimSks($sks) . ' SKS' : '')
                . "\n   • Jam: {$jam}\n   • Ruang: {$ruang}\n   • Dosen: {$dosen}";
            $no++;
        }

        $total = count($items);
        if ($total > 30) {
            $lines[] = "\n*(dan " . ($total - 30) . ' jadwal lainnya)*';
        }

        $totalSks = array_sum(array_map(fn ($r) => (float) ($r['sks_mk'] ?? 0), $items));
        if ($totalSks > 0) {
            $lines[] = "\n**Total mata kuliah**: {$total} — **Total SKS**: " . $this->trimSks($totalSks);
        }

        $lines[] = "\nPastikan datang tepat waktu dan jangan lupa presensi kehadiran via SSO ya kak!";

        return implode("\n", $lines);
    }

    /**
     * Format penyajian status KRS.
     */
    protected function formatKrsResponse(string $nim, mixed $data): string
    {
        if (!is_array($data)) {
            return "Berikut data KRS untuk NIM **{$nim}**:\n\n" . json_encode($data, JSON_PRETTY_PRINT);
        }

        $items = isset($data[0]) ? $data : [$data];
        $count = count($items);

        $lines = [
            "Data KRS resmi untuk NIM **{$nim}**:",
            "• **Total Mata Kuliah Diprogram**: {$count} Mata Kuliah",
        ];

        $semester = $items[0]['nm_smt'] ?? null;
        if ($semester) {
            $lines[] = "• **Semester**: {$semester}";
        }

        $lines[] = "\nDaftar mata kuliah yang diambil:";

        $totalSks = 0;
        foreach (array_slice($items, 0, 8) as $row) {
            $mk = $row['nama_matkul'] ?? $row['matakuliah'] ?? $row['nama_matakuliah'] ?? 'Mata Kuliah';
            $sks = $this->trimSks($row['sks_mk'] ?? $row['sks'] ?? null);
            $totalSks += (float) ($row['sks_mk'] ?? $row['sks'] ?? 0);
            $kelas = $row['nama_kelas'] ?? null;

            $lines[] = '• ' . $mk . ($kelas ? " (Kelas {$kelas})" : '') . " — {$sks} SKS";
        }

        if ($count > 8) {
            $lines[] = '• *(dan ' . ($count - 8) . ' mata kuliah lainnya)*';
        }

        $lines[] = "\n**Total SKS diambil**: " . $this->trimSks($totalSks);
        $lines[] = "\nKRS ini sudah tercatat di sistem akademik SSO UNUJA kak.";

        return implode("\n", $lines);
    }

    /**
     * Deteksi sapaan awal.
     */
    protected function isGreeting(string $text): bool
    {
        $greetings = ['halo', 'hai', 'hello', 'ping', 'p', 'assalamualaikum', 'assalamu\'alaikum', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam', 'bantu saya', 'naja'];
        foreach ($greetings as $greet) {
            if ($text === $greet || str_starts_with($text, $greet . ' ')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Deteksi topik di luar konteks kampus.
     */
    protected function isOutOfScope(string $text): bool
    {
        $keywords = ['resep', 'masak', 'pemilu', 'politik', 'presiden', 'pacar', 'jodoh', 'game', 'mobile legends', 'free fire', 'crypto', 'bitcoin'];
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Apakah pesan hanya berisi NIM (mis. "2521300001", "NIM saya 2521300001")?
     */
    protected function isBareNim(string $text, string $nim): bool
    {
        $cleaned = preg_replace('/\b(nim|nomor|no|saya|aku|nih|kak|ya|dong|adalah)\b/iu', ' ', strtolower($text));
        $digits = preg_replace('/\D+/', '', (string) $cleaned);
        $sisaKata = trim(preg_replace('/[^a-z]+/u', ' ', (string) $cleaned));

        // Harus benar-benar hanya angka NIM, tanpa kata perintah lain
        // seperti "jadwal", "krs", atau "transkrip".
        return $sisaKata === '' && $digits === preg_replace('/\D+/', '', $nim);
    }

    /**
     * Ekstrak NIM dari teks (pola deretan angka 8-14 digit).
     */
    protected function extractNim(string $text): ?string
    {
        if (preg_match('/\b(\d{8,14})\b/', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
