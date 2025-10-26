<nav x-data="{ mobileOpen: false }" class="sticky top-0 z-30 w-full border-b border-white/60 bg-white/80 backdrop-blur-md no-print" aria-label="Navigasi utama">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('simulator') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-slate-900" aria-label="Beranda AI Skincare Simulator">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-orange-500/10 text-orange-600">AI</span>
            AI Skincare Simulator
        </a>

        <button
            type="button"
            class="inline-flex items-center rounded-xl border border-slate-200 p-2 text-slate-600 transition hover:border-slate-300 md:hidden"
            x-on:click="mobileOpen = !mobileOpen"
            :aria-expanded="mobileOpen"
            aria-controls="primary-navigation"
            aria-label="Buka navigasi"
        >
            <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="mobileOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
            <a href="{{ route('simulator') }}" class="hover:text-slate-900">Simulator</a>
            <a href="{{ route('simulations.history') }}" class="hover:text-slate-900">Riwayat</a>
            <a href="{{ route('ingredients.index') }}" class="hover:text-slate-900">Database Bahan</a>
            <a href="{{ route('docs.index') }}" class="hover:text-slate-900">Dokumentasi</a>
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300">Masuk</a>
            <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">Daftar</a>
        </div>
    </div>

    <div
        id="primary-navigation"
        class="md:hidden"
        x-show="mobileOpen"
        x-transition
        x-cloak
        x-on:click.outside="mobileOpen = false"
    >
        <div class="space-y-3 border-t border-slate-100 bg-white px-4 py-4 text-sm font-medium text-slate-600">
            <a href="{{ route('simulator') }}" class="block rounded-2xl px-3 py-2 hover:bg-slate-50">Simulator</a>
            <a href="{{ route('simulations.history') }}" class="block rounded-2xl px-3 py-2 hover:bg-slate-50">Riwayat</a>
            <a href="{{ route('ingredients.index') }}" class="block rounded-2xl px-3 py-2 hover:bg-slate-50">Database Bahan</a>
            <a href="{{ route('docs.index') }}" class="block rounded-2xl px-3 py-2 hover:bg-slate-50">Dokumentasi</a>
            <div class="flex flex-col gap-3 pt-3">
                <a href="{{ route('login') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-300">Masuk</a>
                <a href="{{ route('register') }}" class="rounded-2xl bg-orange-500 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-orange-600">Daftar</a>
            </div>
        </div>
    </div>
</nav>
