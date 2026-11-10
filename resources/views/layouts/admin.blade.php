<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin') — Naja UNUJA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="min-h-screen lg:flex">

    {{-- Sidebar --}}
    <aside class="w-full lg:w-64 lg:min-h-screen bg-unuja-800 text-white lg:sticky lg:top-0 lg:h-screen flex-shrink-0">
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="h-10 w-10 rounded-xl bg-white/15 flex items-center justify-center font-bold text-lg">N</div>
            <div>
                <p class="font-semibold leading-tight">Naja UNUJA</p>
                <p class="text-[11px] text-emerald-200">Dashboard Admin</p>
            </div>
        </div>

        <nav class="p-3 space-y-1">
            @php
                $navClass = fn (bool $active) => $active
                    ? 'bg-white/15 text-white'
                    : 'text-emerald-100/80 hover:bg-white/10 hover:text-white';
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $navClass(request()->routeIs('admin.dashboard')) }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.mahasiswa.index') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $navClass(request()->routeIs('admin.mahasiswa.*')) }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5a6 6 0 0 0-12 0M9 11.25a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5ZM17.25 11.25a3 3 0 1 0 0-6M19.5 19.5a5.25 5.25 0 0 0-3-4.7"/>
                </svg>
                Data Mahasiswa
            </a>

            <a href="{{ url('/') }}" target="_blank"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $navClass(false) }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 10.5h7.5M8.25 14.25h4.5M4.5 4.5h15v15h-15v-15Z"/>
                </svg>
                Buka Chatbot
            </a>
        </nav>

        <div class="hidden lg:block absolute bottom-0 w-64 p-3">
            <div class="rounded-xl bg-black/15 px-3 py-3 text-xs text-emerald-100/70">
                Data bersumber dari API SSO UNUJA.
            </div>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex-1 min-w-0">
        <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200">
            <div class="flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 py-3">
                <div>
                    <h1 class="text-base sm:text-lg font-semibold text-slate-900">@yield('title', 'Dashboard Admin')</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">@yield('subtitle', 'Panel pengelolaan data kampus')</p>
                </div>

                @auth
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    <p class="font-semibold mb-1">Periksa kembali data berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
