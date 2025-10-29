@props(['simulation'])

<div
    x-data="resultStatusTracker({{ $simulation->id }}, '{{ $simulation->status }}')"
    x-init="init()"
    x-show="status !== 'completed'"
    x-cloak
    class="rounded-3xl border border-dashed border-blue-200 bg-blue-50/80 p-6 text-sm text-slate-700"
>
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-500">Status Simulasi</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900" x-text="friendlyStatus()"></h2>
            <p class="mt-1 text-sm text-slate-600" x-text="statusDescription()"></p>
        </div>
        <div class="text-right">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-blue-500">Progress</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900" x-text="progress ? `${progress}%` : '—'"></p>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="button" class="btn-secondary" x-on:click="fetchStatus" :disabled="isLoading">
            <span x-show="!isLoading">Refresh Sekarang</span>
            <span x-show="isLoading" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                </svg>
                Memuat status...
            </span>
        </button>
        <p class="text-xs text-slate-500">Halaman akan menyegarkan otomatis saat simulasi selesai.</p>
    </div>
    <p
        x-show="error"
        class="mt-3 rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs text-rose-600"
        x-text="error"
    ></p>
</div>
