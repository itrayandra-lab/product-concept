@props(['result' => [], 'simulationId' => null])

@php
    $names = collect(data_get($result, 'product_names', []));
@endphp

<section class="card space-y-6">
    <header>
        <h2 class="text-2xl font-semibold text-slate-900">{{ data_get($result, 'selected_name', 'Nama Produk Belum Tersedia') }}</h2>
        <p class="mt-2 text-base text-slate-600">{{ data_get($result, 'selected_tagline', 'Tagline akan muncul setelah simulasi selesai.') }}</p>
    </header>

    <article class="prose max-w-none text-slate-700">
        {!! nl2br(e(data_get($result, 'description', 'Deskripsi produk akan muncul setelah simulasi selesai.'))) !!}
    </article>

    <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alternatif Nama Produk</p>
        <div class="mt-3 flex flex-wrap gap-3">
            @forelse ($names as $name)
                <button type="button" class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 hover:border-blue-300">
                    {{ $name }}
                </button>
            @empty
                <p class="text-sm text-slate-500">Nama alternatif belum tersedia.</p>
            @endforelse
        </div>
    </div>
</section>
