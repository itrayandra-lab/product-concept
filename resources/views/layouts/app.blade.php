<!DOCTYPE html>
<html lang="id" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim(($title ?? '') . ' | AI Skincare Simulator') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    <a href="#main-content" class="skip-link focus-visible:block">Lewati ke konten utama</a>
    <div class="flex min-h-screen flex-col">
        <x-common.navigation />

        <main id="main-content" class="flex-1 py-10" tabindex="-1">
            <div class="mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>

        <x-common.footer />
    </div>

    <x-common.loading-spinner />
    <x-common.toast-stack />
    @stack('scripts')
</body>
</html>
