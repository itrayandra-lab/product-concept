@props(['result' => []])

@php
    $competitors = collect(data_get($result, 'market_analysis.competitor_analysis', []));
    $pricing = data_get($result, 'market_analysis.target_price_range', []);
@endphp

<section class="card space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="section-title">Inteligensi Pasar</h3>
            <p class="text-sm text-slate-500">Perbandingan harga, positioning, dan rekomendasi kanal distribusi.</p>
        </div>
        <span class="chip">Segment: PREMIUM</span>
    </div>

    <div class="grid gap-4 grid-cols-1">
        <div class="rounded-2xl border border-slate-100 bg-orange-50/60 p-4">
            <p class="text-xs uppercase tracking-[0.3em] text-orange-400">HPP</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">IDR {{ number_format(data_get($pricing, 'min', 0)) }}</p>
            <p class="text-xs text-slate-500">Per unit</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-emerald-50/60 p-4">
            <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">SRP</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">IDR {{ number_format(data_get($pricing, 'recommended', 0)) }}</p>
            <p class="text-xs text-slate-500">Harga rekomendasi retail</p>
        </div>
        <div class="rounded-2xl border border-slate-100 bg-blue-50/60 p-4">
            <p class="text-xs uppercase tracking-[0.3em] text-blue-500">Margin</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ round(((data_get($pricing, 'recommended', 0) - data_get($pricing, 'min', 0)) / data_get($pricing, 'recommended', 1)) * 100) }}%</p>
            <p class="text-xs text-slate-500">Estimasi laba kotor</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-100">
        <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3 text-left">Brand</th>
                    <th class="px-4 py-3 text-left">Highlight</th>
                    <th class="px-4 py-3 text-left">Harga</th>
                    <th class="px-4 py-3 text-left">Channel</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white text-sm text-slate-600">
                @forelse ($competitors as $competitor)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-900">{{ data_get($competitor, 'brand') }}</p>
                            <p class="text-xs text-slate-400">{{ data_get($competitor, 'marketplace') }}</p>
                        </td>
                        <td class="px-4 py-4">{{ data_get($competitor, 'positioning') }}</td>
                        <td class="px-4 py-4 font-semibold text-slate-900">IDR {{ number_format(data_get($competitor, 'price', 0)) }}</td>
                        <td class="px-4 py-4">
                            <span class="text-sm text-slate-500">{{ data_get($competitor, 'volume', 'N/A') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada data kompetitor.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Rekomendasi Strategi</p>
        <p class="mt-2 text-sm text-slate-600">{{ data_get($result, 'strategy', 'Strategi distribusi akan muncul setelah hasil lengkap tersedia.') }}</p>
    </div>
</section>
