<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim(($title ?? 'Masuk') . ' | AI Skincare Simulator') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100 font-sans text-slate-900">
    <a href="#main-content" class="skip-link focus-visible:block">Lewati ke konten utama</a>
    <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-2 text-xl font-semibold text-orange-500">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-orange-100 text-orange-600">AI</span>
            Skincare Simulator
        </a>

        <div id="main-content" tabindex="-1" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl shadow-orange-500/5 ring-1 ring-slate-100">
            {{ $slot ?? '' }}
            @yield('content')
        </div>

        <p class="mt-10 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} AI Skincare Simulator. All rights reserved.
        </p>
    </div>

    <x-common.toast-stack />
    @stack('scripts')
</body>
</html>
