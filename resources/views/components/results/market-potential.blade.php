@props(['result' => []])

@php
    $marketPotential = data_get($result, 'market_potential', []);
    $tam = data_get($marketPotential, 'total_addressable_market', []);
    $revenueProjections = data_get($marketPotential, 'revenue_projections', []);
    $growthOpportunities = data_get($marketPotential, 'growth_opportunities', []);
    $riskFactors = data_get($marketPotential, 'risk_factors', []);
@endphp

<section class="card space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="section-title">Potensi Pasar</h3>
            <p class="text-sm text-slate-500">Analisis mendalam tentang peluang pasar dan proyeksi bisnis untuk produk Anda.</p>
        </div>
        <span class="chip">Market Analysis</span>
    </div>

    {{-- Total Addressable Market --}}
    @if($tam)
        <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-6">
            <h4 class="text-lg font-semibold text-slate-900 mb-4">Total Addressable Market (TAM)</h4>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-600 mb-2">Target Segment</p>
                    <p class="font-medium text-slate-900">{{ data_get($tam, 'segment', 'N/A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-600 mb-2">Estimated Market Size</p>
                    <p class="font-medium text-slate-900">{{ number_format(data_get($tam, 'estimated_size', 0)) }} customers</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-slate-600 mb-2">Market Value</p>
                    <p class="text-2xl font-bold text-emerald-600">IDR {{ number_format(data_get($tam, 'value_idr', 0), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Revenue Projections --}}
    @if($revenueProjections)
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-emerald-50/60 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-emerald-500">Monthly Units</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format(data_get($revenueProjections, 'monthly_units', 0)) }}</p>
                <p class="text-xs text-slate-500">Target penjualan bulanan</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-blue-50/60 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-blue-500">Monthly Revenue</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">IDR {{ number_format(data_get($revenueProjections, 'monthly_revenue', 0), 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">Pendapatan bulanan</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-blue-50/60 p-4">
                <p class="text-xs uppercase tracking-[0.3em] text-blue-500">Yearly Revenue</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">IDR {{ number_format(data_get($revenueProjections, 'yearly_revenue', 0), 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500">Proyeksi tahunan</p>
            </div>
        </div>
    @endif

    {{-- Growth Opportunities --}}
    @if($growthOpportunities && count($growthOpportunities) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-900">Peluang Pertumbuhan</h4>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($growthOpportunities as $opportunity)
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 bg-emerald-50/30">
                        <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <p class="text-sm text-slate-700">{{ $opportunity }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Risk Factors --}}
    @if($riskFactors && count($riskFactors) > 0)
        <div class="space-y-4">
            <h4 class="text-lg font-semibold text-slate-900">Faktor Risiko</h4>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($riskFactors as $risk)
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 bg-blue-50/30">
                        <span class="mt-1 h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>
                        <p class="text-sm text-slate-700">{{ $risk }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Target Market Size --}}
    @php
        $targetMarket = data_get($marketPotential, 'target_market_size', []);
    @endphp
    @if($targetMarket)
        <div class="rounded-2xl border border-slate-100 bg-blue-50/30 p-4">
            <h4 class="text-sm font-semibold text-slate-900 mb-2">Target Market Size</h4>
            <p class="text-sm text-slate-600 mb-2">{{ data_get($targetMarket, 'segment_description', '') }}</p>
            <div class="flex items-center gap-4 text-sm">
                <span class="font-medium text-slate-900">{{ number_format(data_get($targetMarket, 'estimated_customers', 0)) }} customers</span>
                <span class="text-slate-500">•</span>
                <span class="text-slate-600">{{ data_get($targetMarket, 'penetration_rate', '') }}</span>
            </div>
        </div>
    @endif
</section>
