@props(['result' => []])

@php
    $ingredients = collect(data_get($result, 'ingredients_analysis.active_ingredients', []));
@endphp

<section class="card space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="section-title">Analisis Bahan Aktif</h3>
            <p class="text-sm text-slate-500">Komposisi, fungsi utama, dan referensi ilmiah.</p>
        </div>
        <span class="chip">{{ $ingredients->count() }} bahan</span>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Bahan</th>
                    <th class="px-4 py-3 text-left">Manfaat</th>
                    <th class="px-4 py-3 text-left">Konsentrasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white text-sm text-slate-600">
                @forelse ($ingredients as $ingredient)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-900">{{ data_get($ingredient, 'name') }}</p>
                            <p class="text-xs text-slate-400">{{ data_get($ingredient, 'inci_name') }}</p>
                        </td>
                        <td class="px-4 py-3">{{ data_get($ingredient, 'function') }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">
                            {{ data_get($ingredient, 'concentration') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data bahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="space-y-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Referensi Penelitian</p>
        <div class="space-y-3">
            @forelse (collect(data_get($result, 'scientific_references', [])) as $reference)
                <div class="rounded-2xl border border-slate-100 p-4 text-sm">
                    <p class="font-semibold text-slate-900">{{ data_get($reference, 'title') }}</p>
                    <p class="text-xs text-slate-500">{{ implode(', ', data_get($reference, 'authors', [])) }} · {{ data_get($reference, 'year') }}</p>
                    @if ($doi = data_get($reference, 'doi'))
                        <a href="https://doi.org/{{ $doi }}" target="_blank" class="mt-1 inline-flex items-center gap-2 text-xs font-semibold text-orange-600">
                            DOI {{ $doi }}
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5h10m0 0v10m0-10L9 15" />
                            </svg>
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-500">Referensi ilmiah akan muncul setelah simulasi selesai.</p>
            @endforelse
        </div>
    </div>
</section>
