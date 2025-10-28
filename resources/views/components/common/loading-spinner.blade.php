<div
    x-data
    x-show="$store.ui?.isGlobalLoading"
    x-transition.opacity
    class="pointer-events-none fixed inset-0 z-[9999] hidden items-center justify-center bg-white/70 backdrop-blur no-print"
    x-cloak
>
    <div class="flex flex-col items-center gap-4">
        <span class="inline-flex h-14 w-14 animate-spin items-center justify-center rounded-full border-4 border-orange-200 border-t-orange-500"></span>
        <p class="text-sm font-semibold text-slate-600">Memuat data simulasi...</p>
    </div>
</div>
