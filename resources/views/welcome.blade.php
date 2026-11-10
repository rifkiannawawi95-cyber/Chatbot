<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Naja - Asisten Digital Cerdas UNUJA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        unuja: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#15803d',
                            600: '#166534',
                            700: '#14532d',
                            800: '#0d3d21',
                            900: '#082916',
                        },
                        gold: {
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #e2e8f0 100%);
        }
        /* Custom scrollbar */
        #chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        #chat-messages::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        #chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        #chat-messages::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .bubble-bot {
            animation: fadeIn 0.25s ease-out;
        }
        .bubble-user {
            animation: fadeIn 0.25s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .typing-dot {
            animation: typing 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-slate-800 antialiased">

    @php
        $namaLogin = $mahasiswaLogin->nama ?? session('naja.nama');
        $nimLogin = $mahasiswaLogin->nim ?? session('naja.nim');
        $prodiLogin = $mahasiswaLogin->prodi ?? session('naja.prodi');
        $inisialLogin = $namaLogin ? strtoupper(mb_substr(trim($namaLogin), 0, 1)) : 'M';
    @endphp

    <!-- Top Navigation Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-emerald-100 shadow-sm sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 py-3 sm:px-6 flex flex-wrap items-center justify-between gap-2 sm:gap-4">
            <div class="flex items-center space-x-3">
                <!-- Avatar / Logo -->
                <div class="relative">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-700 to-emerald-500 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-emerald-700/20">
                        ✨
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-base sm:text-lg font-bold text-slate-800">Naja</h1>
                        <span class="text-[11px] font-semibold tracking-wide uppercase px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">SSO UNUJA</span>
                    </div>
                    <p class="text-xs text-slate-500">Asisten Digital Cerdas Mahasiswa UNUJA</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center space-x-2 sm:space-x-3">
                @if ($nimLogin)
                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-slate-50/80 px-2.5 py-1.5 shadow-sm sm:px-3">
                        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-700 to-emerald-500 text-sm font-bold text-white">
                            {{ $inisialLogin }}
                        </div>
                        <div class="leading-tight">
                            <p class="text-xs font-bold text-slate-800 sm:text-sm">{{ $namaLogin ?: 'Mahasiswa' }}</p>
                            <p class="text-[11px] text-slate-500">
                                <span class="font-mono">{{ $nimLogin }}</span>@if ($prodiLogin)<span class="hidden sm:inline"> · {{ $prodiLogin }}</span>@endif
                            </p>
                        </div>
                    </div>
                @endif
                <button onclick="resetChat()" title="Mulai Obrolan Baru" class="p-2 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-xl transition duration-150 flex items-center text-xs font-medium space-x-1 border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="hidden sm:inline">Reset Chat</span>
                </button>
                <form method="POST" action="{{ route('naja.logout') }}">
                    @csrf
                    <button type="submit" title="Keluar dari Naja" class="p-2 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition duration-150 flex items-center text-xs font-medium space-x-1 border border-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H21"></path>
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Chat Workspace -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-3 sm:px-6 py-4 flex flex-col">
        <!-- Chat Card Container -->
        <div class="flex-1 bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 flex flex-col overflow-hidden h-[75vh] min-h-[520px]">
            
            <!-- Message List -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4">
                
                <!-- Welcome Bot Message -->
                <div class="flex items-start space-x-3 bubble-bot">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex-shrink-0 flex items-center justify-center text-sm font-bold shadow-sm">
                        N
                    </div>
                    <div class="max-w-[85%] sm:max-w-[75%] bg-slate-50 border border-slate-200/80 rounded-2xl rounded-tl-sm px-4 py-3 text-slate-800 text-sm leading-relaxed shadow-xs">
                        <p class="font-medium text-emerald-800 mb-1">Halo{{ $namaLogin ? ', ' . \Illuminate\Support\Str::title(strtolower($namaLogin)) : '' }}! Saya Naja 👋</p>
                        <p>Saya Asisten Digital UNUJA. Ada yang bisa saya bantu terkait jadwal kuliah, KRS, tagihan keuangan, atau informasi akademik kampus hari ini?</p>
                    </div>
                </div>

            </div>

            <!-- Quick Suggestions Pills -->
            <div class="px-4 py-2.5 bg-slate-50/70 border-t border-slate-100 overflow-x-auto">
                <div class="flex space-x-2 text-xs whitespace-nowrap">
                    <span class="text-slate-400 font-medium self-center text-[11px] uppercase tracking-wider pl-1">Saran:</span>
                    <button onclick="sendQuickMessage('Lihat jadwal kuliah hari ini')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        🗓️ Jadwal Kuliah
                    </button>
                    <button onclick="sendQuickMessage('Berapa tagihan UKT saya?')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        💳 Cek UKT
                    </button>
                    <button onclick="sendQuickMessage('Bagaimana status KRS saya semester ini?')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        📝 Status KRS
                    </button>
                    <button onclick="sendQuickMessage('Berapa IPK dan transkrip saya?')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        📊 IPK & KHS
                    </button>
                    <button onclick="sendQuickMessage('Info kegiatan kepesantrenan')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        🕌 Info Pesantren
                    </button>
                    <button onclick="sendQuickMessage('Kontak layanan BAA dan Keuangan')" class="px-3 py-1.5 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 text-slate-700 border border-slate-200 rounded-full transition shadow-2xs font-medium">
                        📞 Kontak BAA
                    </button>
                </div>
            </div>

            <!-- Input Box -->
            <div class="p-3 sm:p-4 bg-white border-t border-slate-200">
                <form id="chat-form" onsubmit="handleChatSubmit(event)" class="flex items-center space-x-2">
                    <input 
                        type="text" 
                        id="user-input" 
                        autocomplete="off" 
                        placeholder="Ketik pertanyaanmu seputar perkuliahan, UKT, KRS..." 
                        class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    />
                    <button 
                        type="submit" 
                        id="send-btn"
                        class="px-5 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-medium text-sm rounded-xl transition shadow-md shadow-emerald-700/20 flex items-center justify-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>Kirim</span>
                        <svg class="w-4 h-4 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
                <div class="mt-2 text-center">
                    <p class="text-[11px] text-slate-400">
                        Naja menggunakan data terintegrasi SSO Universitas Nurul Jadid (UNUJA).
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-xs text-slate-500">
        &copy; 2026 Universitas Nurul Jadid. Layanan Informasi Mahasiswa Terpadu.
    </footer>

    <!-- JavaScript Handling -->
    <script>
        const chatMessages = document.getElementById('chat-messages');
        const userInput = document.getElementById('user-input');
        const sendBtn = document.getElementById('send-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const najaNama = @json($namaLogin);
        const sapaNama = najaNama
            ? ', ' + najaNama.toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase())
            : '';

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Format raw text with bullet points, bold and linebreaks
        function formatMessage(text) {
            // Escape HTML
            let escaped = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Bold **text**
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold text-slate-900">$1</strong>');
            
            // Convert newlines to <br>
            escaped = escaped.replace(/\n/g, '<br>');

            return escaped;
        }

        function appendUserMessage(text) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start justify-end space-x-3 bubble-user';
            wrapper.innerHTML = `
                <div class="max-w-[85%] sm:max-w-[75%] bg-emerald-700 text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed shadow-md shadow-emerald-800/10">
                    <p>${formatMessage(text)}</p>
                </div>
                <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex-shrink-0 flex items-center justify-center text-xs font-semibold">
                    Kamu
                </div>
            `;
            chatMessages.appendChild(wrapper);
            scrollToBottom();
        }

        function appendBotMessage(text) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start space-x-3 bubble-bot';
            wrapper.innerHTML = `
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex-shrink-0 flex items-center justify-center text-sm font-bold shadow-sm">
                    N
                </div>
                <div class="max-w-[85%] sm:max-w-[75%] bg-slate-50 border border-slate-200/80 rounded-2xl rounded-tl-sm px-4 py-3 text-slate-800 text-sm leading-relaxed shadow-xs">
                    <p>${formatMessage(text)}</p>
                </div>
            `;
            chatMessages.appendChild(wrapper);
            scrollToBottom();
        }

        function showTypingIndicator() {
            const typingWrapper = document.createElement('div');
            typingWrapper.id = 'typing-indicator';
            typingWrapper.className = 'flex items-start space-x-3 bubble-bot';
            typingWrapper.innerHTML = `
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex-shrink-0 flex items-center justify-center text-sm font-bold shadow-sm">
                    N
                </div>
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl rounded-tl-sm px-4 py-3 text-slate-500 text-sm flex items-center space-x-1.5 shadow-xs">
                    <span class="w-2 h-2 bg-emerald-600 rounded-full typing-dot"></span>
                    <span class="w-2 h-2 bg-emerald-600 rounded-full typing-dot"></span>
                    <span class="w-2 h-2 bg-emerald-600 rounded-full typing-dot"></span>
                </div>
            `;
            chatMessages.appendChild(typingWrapper);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            const typing = document.getElementById('typing-indicator');
            if (typing) {
                typing.remove();
            }
        }

        async function handleChatSubmit(event) {
            if (event) event.preventDefault();

            const message = userInput.value.trim();
            if (!message) return;

            // Clear input & disable send
            userInput.value = '';
            userInput.disabled = true;
            sendBtn.disabled = true;

            appendUserMessage(message);
            showTypingIndicator();

            try {
                const response = await fetch("{{ route('chat.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                removeTypingIndicator();

                if (!response.ok) {
                    if (response.status === 401) {
                        appendBotMessage("Sesi login kamu sepertinya sudah habis. Yuk, login ulang ke SSO UNUJA agar saya bisa mengakses datamu.");
                    } else {
                        appendBotMessage("Mohon maaf, sistem kampus sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.");
                    }
                    return;
                }

                const data = await response.json();
                appendBotMessage(data.reply || "Maaf, data belum tersedia.");
            } catch (error) {
                removeTypingIndicator();
                appendBotMessage("Mohon maaf, sistem kampus sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.");
            } finally {
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
            }
        }

        function sendQuickMessage(text) {
            userInput.value = text;
            handleChatSubmit();
        }

        function resetChat() {
            chatMessages.innerHTML = `
                <div class="flex items-start space-x-3 bubble-bot">
                    <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex-shrink-0 flex items-center justify-center text-sm font-bold shadow-sm">
                        N
                    </div>
                    <div class="max-w-[85%] sm:max-w-[75%] bg-slate-50 border border-slate-200/80 rounded-2xl rounded-tl-sm px-4 py-3 text-slate-800 text-sm leading-relaxed shadow-xs">
                        <p class="font-medium text-emerald-800 mb-1">Halo${sapaNama}! Saya Naja 👋</p>
                        <p>Saya Asisten Digital UNUJA. Ada yang bisa saya bantu terkait jadwal kuliah, KRS, tagihan keuangan, atau informasi akademik kampus hari ini?</p>
                    </div>
                </div>
            `;
            userInput.value = '';
            userInput.focus();
        }

        // Focus input on load
        window.addEventListener('DOMContentLoaded', () => {
            userInput.focus();
        });
    </script>
</body>
</html>
