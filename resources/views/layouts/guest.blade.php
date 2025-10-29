<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim(($title ?? 'Simulasi Produk') . ' | AI Skincare Simulator') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <a href="#main-content" class="skip-link focus-visible:block">Lewati ke konten utama</a>
    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="hidden w-full max-w-sm border-r border-slate-100 bg-white px-6 py-8 lg:flex lg:flex-col">
            <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-3 text-lg font-semibold text-slate-900">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/10 text-blue-600">AI</span>
                AI Skincare Simulator
            </a>
            <div class="mt-10 space-y-8">
                @guest
                    <div class="space-y-3">
                        <a href="{{ route('login') }}" class="block w-full rounded-xl border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="block w-full rounded-xl bg-blue-500 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-blue-600">
                            Daftar
                        </a>
                    </div>
                @endguest

                @auth
                    <div class="space-y-3">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-semibold text-slate-500">Selamat datang</p>
                            <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-300">
                                Keluar
                            </button>
                        </form>
                    </div>
                @endauth

                <div>
                    <p class="text-sm uppercase tracking-wide text-slate-400">Langkah Simulasi</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Detail Produk</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Target Pasar</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Komposisi</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-500"></span> Konfigurasi Lanjut</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-700">Butuh bantuan?</p>
                    <p class="mt-2 text-sm text-slate-500">Hubungi tim R&D kami via WhatsApp untuk konsultasi cepat.</p>
                    <a href="https://wa.me/{{ config('services.whatsapp.business_number') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-500 px-4 py-2 text-sm font-medium text-white">Hubungi Kami</a>
                </div>
            </div>
            <p class="mt-auto text-xs text-slate-400">&copy; {{ date('Y') }} AI Skincare Simulator</p>
        </aside>

        <main class="flex-1" id="main-content" tabindex="-1">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <!-- Mobile Auth Buttons (visible on small screens) -->
    <div class="fixed bottom-4 left-4 right-4 z-50 lg:hidden">
        @guest
            <div class="flex gap-3">
                <a href="{{ route('login') }}" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-700 shadow-lg">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="flex-1 rounded-xl bg-blue-500 px-4 py-3 text-center text-sm font-semibold text-white shadow-lg">
                    Daftar
                </a>
            </div>
        @endguest

        @auth
            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
                <div class="flex-1">
                    <p class="text-xs text-slate-500">Selamat datang</p>
                    <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700">
                        Keluar
                    </button>
                </form>
            </div>
        @endauth
    </div>

    <x-common.toast-stack />
    @stack('scripts')
</body>
</html>
