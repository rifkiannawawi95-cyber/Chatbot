<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naja SSO UNUJA — Asisten Digital Cerdas Mahasiswa UNUJA</title>
    <meta name="description" content="Naja adalah asisten digital cerdas mahasiswa UNUJA yang terintegrasi dengan SSO. Tanya jadwal kuliah, KRS, transkrip, IPK, dan informasi akademik langsung dari data resmi kampus.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        unuja: {
                            50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0',
                            500: '#15803d', 600: '#166534', 700: '#14532d',
                            800: '#0d3d21', 900: '#082916',
                        },
                    },
                    keyframes: {
                        floaty: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                    },
                    animation: { floaty: 'floaty 6s ease-in-out infinite' },
                },
            },
        };
    </script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-bg {
            background-image:
                linear-gradient(to right, rgba(22, 101, 52, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(22, 101, 52, 0.06) 1px, transparent 1px);
            background-size: 42px 42px;
        }
        .fab-ping::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: rgba(21, 128, 61, 0.35);
            animation: fabPing 2.2s cubic-bezier(0, 0, 0.2, 1) infinite;
            z-index: -1;
        }
        @keyframes fabPing {
            0% { transform: scale(1); opacity: 0.7; }
            75%, 100% { transform: scale(1.7); opacity: 0; }
        }
        .brand-gate { -webkit-touch-callout: none; -webkit-user-select: none; user-select: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <!-- ================= NAVBAR ================= -->
    <header class="sticky top-0 z-40 border-b border-emerald-100/80 bg-white/85 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5 sm:px-6">
            <a href="{{ route('landing') }}" class="brand-gate flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-unuja-700 to-emerald-500 text-lg font-bold text-white shadow-lg shadow-unuja-700/20">
                    ✨
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-extrabold tracking-tight text-slate-900">Naja</span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-unuja-700">SSO UNUJA</span>
                    </div>
                    <p class="text-xs text-slate-500">Asisten Digital Cerdas Mahasiswa</p>
                </div>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex">
                <a href="#fitur" class="transition hover:text-unuja-700">Fitur</a>
                <a href="#cara-kerja" class="transition hover:text-unuja-700">Cara Kerja</a>
                <a href="#privasi" class="transition hover:text-unuja-700">Data &amp; Privasi</a>
                <a href="#faq" class="transition hover:text-unuja-700">FAQ</a>
            </nav>

            <a href="{{ route('naja.login') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-unuja-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-unuja-800 focus:outline-none focus:ring-2 focus:ring-unuja-600/40">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H21"/>
                </svg>
                Masuk
            </a>
        </div>
    </header>

    <!-- ================= HERO ================= -->
    <section class="relative overflow-hidden">
        <div class="grid-bg absolute inset-0 opacity-70"></div>
        <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl"></div>
        <div class="absolute -right-24 top-32 h-80 w-80 rounded-full bg-teal-100/60 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl gap-12 px-4 pb-16 pt-14 sm:px-6 lg:grid-cols-2 lg:items-center lg:pt-20">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-unuja-700 shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-600"></span>
                    </span>
                    Terintegrasi langsung dengan SSO UNUJA
                </span>

                <h1 class="mt-5 text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl lg:text-[3.4rem]">
                    Naja <span class="text-unuja-700">SSO UNUJA</span><br>
                    <span class="bg-gradient-to-r from-unuja-700 to-emerald-500 bg-clip-text text-transparent">Asisten Digital Cerdas</span><br>
                    Mahasiswa UNUJA
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                    Tanya apa saja soal jadwal kuliah, KRS, transkrip, IPK, dan informasi akademik.
                    Naja menjawab cepat dengan data resmi dari sistem kampus — cukup masuk memakai NIM kamu.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('naja.login') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-unuja-700 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-unuja-700/25 transition hover:-translate-y-0.5 hover:bg-unuja-800">
                        Mulai Ngobrol dengan Naja
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                    <a href="#fitur"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Lihat Fitur
                    </a>
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-medium text-slate-500">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Gratis untuk mahasiswa
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Login dengan NIM
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Data resmi kampus
                    </span>
                </div>
            </div>

            <!-- Mock chat -->
            <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-tr from-emerald-200/50 to-teal-100/40 blur-2xl"></div>
                <div class="relative animate-floaty rounded-[1.75rem] border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-300/40">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-unuja-700 to-emerald-500 text-sm font-bold text-white">N</div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Naja</p>
                                <p class="flex items-center gap-1 text-[11px] text-emerald-600">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Online
                                </p>
                            </div>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700">SSO</span>
                    </div>

                    <div class="space-y-3 py-4">
                        <div class="flex justify-end">
                            <p class="max-w-[80%] rounded-2xl rounded-tr-sm bg-unuja-700 px-3.5 py-2.5 text-xs text-white shadow-sm">
                                Naja, jadwal kuliah saya hari ini apa?
                            </p>
                        </div>
                        <div class="flex items-start gap-2">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-[11px] font-bold text-white">N</div>
                            <div class="max-w-[82%] rounded-2xl rounded-tl-sm border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs leading-relaxed text-slate-700">
                                <p class="font-semibold text-unuja-800">Hari ini kamu punya 2 kelas:</p>
                                <p class="mt-1">• 08.00 — Pemrograman Web<br>• 13.00 — Basis Data</p>
                                <p class="mt-1 text-[11px] text-slate-500">Ruang lab TI 2, dosen Pak Ahmad.</p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <p class="max-w-[80%] rounded-2xl rounded-tr-sm bg-unuja-700 px-3.5 py-2.5 text-xs text-white shadow-sm">
                                Berapa IPK saya?
                            </p>
                        </div>
                        <div class="flex items-start gap-2">
                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-[11px] font-bold text-white">N</div>
                            <div class="max-w-[82%] rounded-2xl rounded-tl-sm border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs leading-relaxed text-slate-700">
                                IPK kamu <span class="font-bold text-emerald-700">3.62</span> dari 132 SKS. Pertahankan ya! 🎓
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <span class="flex-1 text-xs text-slate-400">Ketik pertanyaanmu…</span>
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-unuja-700 text-white">
                            <svg class="h-3.5 w-3.5 rotate-45" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= STATS ================= -->
    <section class="border-y border-emerald-100 bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-4 py-8 sm:px-6 md:grid-cols-4">
            @foreach ([
                [number_format($totalMahasiswa, 0, ',', '.'), 'Mahasiswa terdata'],
                [(string) $totalProdi, 'Program studi'],
                ['24/7', 'Siap menjawab'],
                ['100%', 'Data dari SSO'],
            ] as $stat)
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-unuja-700 sm:text-3xl">{{ $stat[0] }}</p>
                    <p class="mt-1 text-xs font-medium text-slate-500 sm:text-sm">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ================= FITUR ================= -->
    <section id="fitur" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-20">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Fitur</span>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Semua info akademik dalam satu obrolan</h2>
            <p class="mt-3 text-base text-slate-600">Naja memahami pertanyaanmu dan mengambil jawaban langsung dari API resmi SSO UNUJA.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $fitur = [
                    ['🗓️', 'Jadwal Kuliah', 'Lihat jadwal kuliah sesuai program studi dan semester aktif kamu, lengkap dengan jam, ruang, dan dosen.'],
                    ['📊', 'Transkrip & IPK', 'Cek IPK, total SKS lulus, dan rincian nilai mata kuliah dari data akademik resmi.'],
                    ['📝', 'Status KRS', 'Pantau mata kuliah yang sudah diambil pada semester berjalan dengan cepat.'],
                    ['🎓', 'Data Mahasiswa', 'Identitas akademik, program studi, dosen wali, dan angkatan tampil rapi dalam satu jawaban.'],
                    ['🔗', 'Terintegrasi SSO', 'Semua jawaban bersumber dari SSO UNUJA, bukan data tebakan atau rekayasa.'],
                    ['⚡', 'Cepat & Praktis', 'Tanpa perlu membuka banyak menu. Cukup ketik pertanyaan, Naja yang merangkum.'],
                ];
            @endphp

            @foreach ($fitur as $item)
                <div class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-2xl ring-1 ring-emerald-100 transition group-hover:bg-emerald-100">{{ $item[0] }}</div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $item[1] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $item[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ================= CARA KERJA ================= -->
    <section id="cara-kerja" class="scroll-mt-20 border-y border-emerald-100 bg-gradient-to-b from-emerald-50/60 to-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Cara Kerja</span>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Mulai dalam tiga langkah</h2>
                <p class="mt-3 text-base text-slate-600">Tidak perlu daftar akun baru. Gunakan NIM yang sudah terdaftar di kampus.</p>
            </div>

            <div class="relative mt-12 grid gap-6 md:grid-cols-3">
                @php
                    $langkah = [
                        ['01', 'Masuk dengan NIM', 'Buka halaman login, isi NIM dan kata sandi yang memuat NIM kamu.'],
                        ['02', 'Tanya Naja', 'Tanyakan jadwal, KRS, transkrip, IPK, atau informasi akademik lainnya.'],
                        ['03', 'Dapatkan jawaban', 'Naja mengambil data resmi dari SSO dan menyajikannya dengan bahasa yang mudah dipahami.'],
                    ];
                @endphp

                @foreach ($langkah as $step)
                    <div class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <span class="text-4xl font-extrabold text-emerald-100">{{ $step[0] }}</span>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">{{ $step[1] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ================= PRIVASI ================= -->
    <section id="privasi" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">Data &amp; Privasi</span>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Transparan tentang sumber data</h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">
                    Naja membaca data melalui API resmi SSO Universitas Nurul Jadid. Kami tidak menyimpan kata sandi
                    dan tidak mengubah data akademik kamu.
                </p>

                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    @foreach ([
                        'Jawaban diambil langsung dari layanan data SSO UNUJA.',
                        'Field pribadi yang dibatasi server SSO tetap dilindungi.',
                        'Sesi login hanya berlaku di perangkatmu dan bisa diakhiri kapan saja.',
                    ] as $poin)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            {{ $poin }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-3xl border border-emerald-100 bg-gradient-to-br from-unuja-800 to-unuja-700 p-7 text-white shadow-xl shadow-unuja-800/20">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-2xl">🛡️</div>
                <h3 class="mt-4 text-xl font-bold">Aman &amp; terpercaya</h3>
                <p class="mt-2 text-sm leading-relaxed text-emerald-100/90">
                    Naja hanya menampilkan informasi yang memang diperuntukkan bagi mahasiswa. Data yang dibatasi oleh
                    kebijakan SSO tidak akan dibuka paksa.
                </p>

                <div class="mt-6 space-y-3 rounded-2xl bg-black/15 p-4 text-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-2">
                        <span class="text-emerald-100/70">Sumber data</span>
                        <span class="font-semibold">SSO UNUJA</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-2">
                        <span class="text-emerald-100/70">Metode masuk</span>
                        <span class="font-semibold">NIM + kata sandi</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-emerald-100/70">Biaya</span>
                        <span class="font-semibold">Gratis</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FAQ ================= -->
    <section id="faq" class="scroll-mt-20 border-t border-emerald-100 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:py-20">
            <div class="text-center">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600">FAQ</span>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Pertanyaan yang sering diajukan</h2>
            </div>

            <div class="mt-10 space-y-3">
                @php
                    $faq = [
                        ['Apa itu Naja?', 'Naja adalah asisten digital cerdas untuk mahasiswa UNUJA. Ia menjawab pertanyaan seputar akademik dengan mengambil data dari SSO UNUJA.'],
                        ['Bagaimana cara masuk?', 'Buka halaman login, masukkan NIM kamu, lalu isi kolom kata sandi dengan NIM tersebut. Contoh: NIM 2521300001, kata sandi 2521300001.'],
                        ['Apakah saya perlu daftar akun?', 'Tidak. Naja memakai NIM yang sudah terdaftar di data mahasiswa kampus.'],
                        ['Apakah data saya aman?', 'Naja tidak menyimpan kata sandi. Sesi login tersimpan di perangkatmu dan bisa diakhiri lewat tombol keluar.'],
                        ['Apakah semua data bisa ditampilkan?', 'Naja menampilkan data yang tersedia dan diizinkan oleh API SSO. Sebagian field pribadi memang dibatasi oleh kebijakan server kampus.'],
                    ];
                @endphp

                @foreach ($faq as $item)
                    <details class="group rounded-2xl border border-slate-200 bg-slate-50/60 px-5 py-4 transition open:bg-white open:shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-slate-800">
                            {{ $item[0] }}
                            <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $item[1] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="px-4 pb-20 pt-4 sm:px-6">
        <div class="mx-auto max-w-6xl overflow-hidden rounded-3xl bg-gradient-to-r from-unuja-800 via-unuja-700 to-emerald-700 px-6 py-12 text-center shadow-xl shadow-unuja-800/20 sm:px-12">
            <h2 class="text-2xl font-extrabold text-white sm:text-3xl">Siap bertanya pada Naja?</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm text-emerald-100/90 sm:text-base">
                Masuk dengan NIM kamu dan mulai dapatkan jawaban akademik dalam hitungan detik.
            </p>
            <a href="{{ route('naja.login') }}"
               class="mt-7 inline-flex items-center gap-2 rounded-2xl bg-white px-7 py-3.5 text-sm font-bold text-unuja-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-emerald-50">
                Masuk ke Naja
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 sm:flex-row sm:px-6">
            <a href="{{ route('landing') }}" class="brand-gate flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-unuja-700 text-sm font-bold text-white">N</div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Naja SSO UNUJA</p>
                    <p class="text-xs text-slate-500">Asisten Digital Cerdas Mahasiswa UNUJA</p>
                </div>
            </a>
            <p class="text-xs text-slate-500">&copy; {{ date('Y') }} Universitas Nurul Jadid. Layanan Informasi Mahasiswa Terpadu.</p>
        </div>
    </footer>

    <!-- ================= FLOATING BUTTON (pojok kanan bawah) ================= -->
    <div class="fixed bottom-5 right-5 z-50 sm:bottom-7 sm:right-7">
        <a href="{{ route('naja.login') }}"
           aria-label="Masuk ke Naja"
           class="fab-ping group relative inline-flex items-center gap-2.5 rounded-full bg-gradient-to-r from-unuja-700 to-emerald-600 px-5 py-3.5 text-sm font-bold text-white shadow-2xl shadow-emerald-900/30 transition hover:-translate-y-0.5 hover:shadow-emerald-900/40 focus:outline-none focus:ring-4 focus:ring-emerald-400/40">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                </svg>
            </span>
            <span>Masuk Naja</span>
        </a>
    </div>

    <!-- Akses admin tersembunyi: klik logo (navbar & footer) 3x beruntun,
         atau tahan logo selama ~1 detik. -->
    <script>
        (function () {
            const adminUrl = @json(route('admin.login'));

            document.querySelectorAll('.brand-gate').forEach(function (el) {
                let count = 0;
                let resetTimer = null;
                let holdTimer = null;
                let suppressClick = false;
                let startX = 0;
                let startY = 0;

                const clearHold = () => { clearTimeout(holdTimer); holdTimer = null; };

                el.addEventListener('pointerdown', function (e) {
                    startX = e.clientX;
                    startY = e.clientY;
                    suppressClick = false;
                    clearHold();
                    holdTimer = setTimeout(function () {
                        suppressClick = true;
                        window.location.href = adminUrl;
                    }, 900);
                });

                el.addEventListener('pointermove', function (e) {
                    if (Math.hypot(e.clientX - startX, e.clientY - startY) > 12) clearHold();
                });

                ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) {
                    el.addEventListener(ev, clearHold);
                });

                el.addEventListener('contextmenu', function (e) {
                    if (holdTimer) e.preventDefault();
                });

                el.addEventListener('click', function (e) {
                    if (suppressClick) { e.preventDefault(); suppressClick = false; return; }
                    if (e.detail === 0) return; // keyboard Enter: biarkan navigasi normal

                    e.preventDefault();
                    count++;

                    if (count >= 3) {
                        clearTimeout(resetTimer);
                        window.location.href = adminUrl;
                        return;
                    }

                    clearTimeout(resetTimer);
                    resetTimer = setTimeout(function () {
                        count = 0;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 450);
                });
            });
        })();
    </script>

</body>
</html>
