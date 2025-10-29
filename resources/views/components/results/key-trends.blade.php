@props(['result' => []])

@php
    $keyTrends = data_get($result, 'key_trends', []);
    $trendingIngredients = data_get($keyTrends, 'trending_ingredients', []);
    $marketMovements = data_get($keyTrends, 'market_movements', []);
    $competitiveLandscape = data_get($keyTrends, 'competitive_landscape', '');
@endphp

<section class="card space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="section-title">Tren Kunci Pasar</h3>
            <p class="text-sm text-slate-500">Insight tren terkini dalam industri skincare dan preferensi konsumen.</p>
        </div>
        <span class="chip">Market Intelligence</span>
    </div>

    {{-- Trending Ingredients --}}
    @if($trendingIngredients && count($trendingIngredients) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-900">Bahan Aktif Trending</h4>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($trendingIngredients as $ingredient)
                    @php
                        $trendStatus = data_get($ingredient, 'trend_status', '');
                        $statusColor = match($trendStatus) {
                            'Peak' => 'bg-red-100 text-red-800 border-red-200',
                            'Rising' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'Steady' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'Declining' => 'bg-gray-100 text-gray-800 border-gray-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200'
                        };
                    @endphp
                    <div class="rounded-xl border border-slate-100 bg-white p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h5 class="font-semibold text-slate-900">{{ data_get($ingredient, 'name', '') }}</h5>
                            <span class="px-2 py-1 text-xs font-medium rounded-full border {{ $statusColor }}">
                                {{ $trendStatus }}
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Search Trend</span>
                                <span class="font-medium text-slate-900">{{ data_get($ingredient, 'google_search_trend', 'N/A') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-600">Awareness</span>
                                <span class="font-medium text-slate-900">{{ data_get($ingredient, 'consumer_awareness', 'N/A') }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">{{ data_get($ingredient, 'social_media_mentions', '') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Market Movements --}}
    @if($marketMovements && count($marketMovements) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-900">Gerakan Pasar</h4>
            <div class="space-y-3">
                @foreach($marketMovements as $movement)
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 bg-slate-50/60">
                        <span class="mt-1 h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>
                        <p class="text-sm text-slate-700">{{ $movement }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Competitive Landscape --}}
    @if($competitiveLandscape)
        <div class="rounded-2xl border border-slate-100 bg-blue-50/30 p-4">
            <h4 class="text-sm font-semibold text-slate-900 mb-2">Landscape Kompetitif</h4>
            <p class="text-sm text-slate-700">{{ $competitiveLandscape }}</p>
        </div>
    @endif
</section>
