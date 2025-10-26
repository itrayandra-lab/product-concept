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
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 text-orange-600">AI</span>
                AI Skincare Simulator
            </a>
            <div class="mt-10 space-y-8">
                <div>
                    <p class="text-sm uppercase tracking-wide text-slate-400">Langkah Simulasi</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> Detail Produk</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> Target Pasar</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> Komposisi</li>
                        <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-orange-500"></span> Konfigurasi Lanjut</li>
                    </ul>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-700">Butuh bantuan?</p>
                    <p class="mt-2 text-sm text-slate-500">Hubungi tim R&D kami via WhatsApp untuk konsultasi cepat.</p>
                    <a href="https://wa.me/6281111111" class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-500 px-4 py-2 text-sm font-medium text-white">Hubungi Kami</a>
                </div>
            </div>
            <p class="mt-auto text-xs text-slate-400">&copy; {{ date('Y') }} AI Skincare Simulator</p>
        </aside>

        <main class="flex-1" id="main-content" tabindex="-1">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    <x-common.toast-stack />
    @stack('scripts')
</body>
</html>
