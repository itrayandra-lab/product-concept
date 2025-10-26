@props(['result' => [], 'simulationId' => null])

@php
    $names = collect(data_get($result, 'product_names', []));
@endphp

<section class="card space-y-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.3em] text-orange-400">Hasil AI</p>
            <h2 class="text-2xl font-semibold text-slate-900">{{ data_get($result, 'product_name', 'Nama Produk Belum Tersedia') }}</h2>
            <p class="mt-2 text-base text-slate-600">{{ data_get($result, 'tagline', 'Tagline akan muncul setelah simulasi selesai.') }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                class="btn-secondary"
                x-on:click="$store.export.export({{ $simulationId ?? 'null' }}, 'pdf')"
                x-bind:disabled="$store.export.isExporting"
                :aria-busy="$store.export.isExporting"
            >
                <span x-show="!$store.export.isExporting">Download PDF</span>
                <span x-show="$store.export.isExporting" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                    </svg>
                    Menyiapkan...
                </span>
            </button>
            <button
                type="button"
                class="btn-primary"
                x-on:click="$store.export.export({{ $simulationId ?? 'null' }}, 'docx')"
                x-bind:disabled="$store.export.isExporting"
                :aria-busy="$store.export.isExporting"
            >
                <span x-show="!$store.export.isExporting">Download Word</span>
                <span x-show="$store.export.isExporting" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                    </svg>
                    Menyiapkan...
                </span>
            </button>
            <button
                type="button"
                class="btn-secondary"
                x-on:click="$store.export.export({{ $simulationId ?? 'null' }}, 'png')"
                x-bind:disabled="$store.export.isExporting"
                :aria-busy="$store.export.isExporting"
            >
                <span x-show="!$store.export.isExporting">Download PNG</span>
                <span x-show="$store.export.isExporting" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                    </svg>
                    Menyiapkan...
                </span>
            </button>
        </div>
    </header>

    <article class="prose max-w-none text-slate-700">
        {!! nl2br(e(data_get($result, 'description', 'Deskripsi produk akan muncul setelah simulasi selesai.'))) !!}
    </article>

    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alternatif Nama Produk</p>
        <div class="mt-3 flex flex-wrap gap-3">
            @forelse ($names as $name)
                <button type="button" class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-600 hover:border-orange-300">
                    {{ $name }}
                </button>
            @empty
                <p class="text-sm text-slate-500">Nama alternatif belum tersedia.</p>
            @endforelse
        </div>
    </div>
</section>
