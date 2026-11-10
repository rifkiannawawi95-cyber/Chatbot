<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Naja — SSO UNUJA</title>
    <meta name="description" content="Masuk ke Naja, asisten digital cerdas mahasiswa UNUJA, menggunakan NIM.">
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
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .grid-bg {
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 40px 40px;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="grid w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-300/50 lg:grid-cols-2">

            <!-- Panel branding -->
            <aside class="relative hidden overflow-hidden bg-gradient-to-br from-unuja-800 via-unuja-700 to-emerald-700 p-9 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="grid-bg absolute inset-0 opacity-60"></div>
                <div class="absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-emerald-400/20 blur-3xl"></div>

                <div class="relative">
                    <a href="{{ route('landing') }}" class="inline-flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl font-bold backdrop-blur">✨</div>
                        <div>
                            <p class="text-lg font-extrabold leading-tight">Naja</p>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">SSO UNUJA</p>
                        </div>
                    </a>

                    <h2 class="mt-12 text-3xl font-extrabold leading-tight">
                        Asisten Digital<br>Cerdas Mahasiswa<br>UNUJA
                    </h2>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-emerald-100/90">
                        Satu obrolan untuk jadwal kuliah, KRS, transkrip, IPK, dan informasi akademik dari data resmi kampus.
                    </p>
                </div>

                <ul class="relative mt-10 space-y-3 text-sm text-emerald-100/90">
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        Data resmi dari SSO UNUJA
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        Jawaban cepat dan mudah dipahami
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/15">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </span>
                        Gratis untuk mahasiswa UNUJA
                    </li>
                </ul>
            </aside>

            <!-- Form -->
            <main class="p-7 sm:p-10">
                <div class="mb-7 flex items-center justify-between">
                    <a href="{{ route('landing') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-unuja-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Beranda
                    </a>
                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-unuja-700">Mahasiswa</span>
                </div>

                <div class="lg:hidden">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-unuja-700 to-emerald-500 text-lg font-bold text-white">✨</div>
                        <div>
                            <p class="text-base font-extrabold text-slate-900">Naja</p>
                            <p class="text-xs text-slate-500">Asisten Digital Cerdas UNUJA</p>
                        </div>
                    </div>
                </div>

                <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Masuk ke Naja</h1>
                <p class="mt-2 text-sm text-slate-500">Gunakan NIM kamu untuk mengakses asisten digital kampus.</p>

                @if (session('error'))
                    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('naja.login.attempt') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="nim" class="mb-1.5 block text-sm font-semibold text-slate-700">NIM (Nomor Induk Mahasiswa)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5h-15A1.5 1.5 0 0 0 3 6v12a1.5 1.5 0 0 0 1.5 1.5Zm0 0a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5m0 15a1.5 1.5 0 0 0 1.5-1.5V7.5A1.5 1.5 0 0 0 6 6m0 0h6a1.5 1.5 0 0 1 1.5 1.5v10.5A1.5 1.5 0 0 1 12 19.5H6"/></svg>
                            </span>
                            <input id="nim" name="nim" type="text" inputmode="numeric" autocomplete="username"
                                   value="{{ old('nim') }}" required autofocus
                                   placeholder="Contoh: 2521300001"
                                   class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata sandi</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            </span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   placeholder="Masukkan NIM kamu"
                                   class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20">
                        </div>
                        <p class="mt-1.5 text-xs text-slate-500">
                            Kata sandi wajib memuat NIM kamu. Contoh: NIM <span class="font-semibold text-slate-700">2521300001</span>, kata sandi <span class="font-semibold text-slate-700">2521300001</span>.
                        </p>
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-unuja-700 px-4 py-3 text-sm font-bold text-white shadow-md shadow-unuja-700/25 transition hover:bg-unuja-800 focus:outline-none focus:ring-2 focus:ring-unuja-600/40">
                        Masuk ke Naja
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </button>
                </form>

                <p class="mt-6 text-center text-xs leading-relaxed text-slate-400">
                    Dengan masuk, kamu menyetujui penggunaan data akademik sesuai kebijakan SSO UNUJA.<br>
                    Naja tidak menyimpan kata sandi kamu.
                </p>
            </main>
        </div>
    </div>
</body>
</html>
