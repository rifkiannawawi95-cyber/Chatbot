<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Dashboard — Naja UNUJA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        unuja: {
                            50: '#f0fdf4', 100: '#dcfce7', 500: '#15803d',
                            600: '#166534', 700: '#14532d', 800: '#0d3d21', 900: '#082916',
                        },
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="mx-auto h-14 w-14 rounded-2xl bg-unuja-700 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-unuja-700/20">N</div>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">Dashboard Admin</h1>
            <p class="text-sm text-slate-500">Kelola data mahasiswa Naja UNUJA</p>
        </div>

        <div class="rounded-2xl bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-200">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email admin</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20"
                           placeholder="admin@unuja.ac.id">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Kata sandi</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-unuja-600 focus:ring-2 focus:ring-unuja-600/20"
                           placeholder="••••••••">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-unuja-600 focus:ring-unuja-600">
                    Ingat saya
                </label>

                <button type="submit"
                        class="w-full rounded-xl bg-unuja-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-unuja-800 focus:outline-none focus:ring-2 focus:ring-unuja-600/40">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Pusat Data &amp; Sistem Informasi Universitas Nurul Jadid
        </p>
    </div>
</body>
</html>
