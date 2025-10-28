@extends('layouts.app', ['title' => 'Hasil Simulasi'])

@section('content')
    @php
        $simulationResult = $result ?? [];
    @endphp

    <div class="space-y-8">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Ringkasan Produk</h1>
                <p class="mt-1 text-sm text-slate-500">Dihasilkan pada {{ optional($simulation->created_at)->format('d F Y, H:i') }} · Status: <span class="font-semibold text-emerald-600">{{ ucfirst($simulation->status) }}</span></p>
            </div>
            <div class="flex gap-3 no-print">
                <button type="button" class="btn-secondary" onclick="window.location.href='{{ url('/simulator') }}'">Buat Simulasi Baru</button>
                <div class="relative" x-data="{ open: false }">
                    <button type="button" class="btn-primary" @click="open = !open">
                        Download
                        <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-2 shadow-lg z-50">
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" onclick="exportSimulation('pdf')">
                            📄 Download PDF
                        </button>
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" onclick="exportSimulation('docx')">
                            📝 Download Word
                        </button>
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50" onclick="exportSimulation('json')">
                            📊 Download JSON
                        </button>
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="window.print()">Print</button>
            </div>
        </header>

        <x-results.status-card :simulation="$simulation" />

        @if ($simulation->status === 'completed')
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <x-results.product-overview :result="$result" :simulation-id="$simulation->id" />
                    <x-results.ingredients-table :result="$result" />
                </div>
                <div class="space-y-6">
                    <x-results.market-analysis :result="$result" />
                    <section class="card space-y-4">
                        <h3 class="section-title">Langkah Selanjutnya</h3>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-orange-500"></span>
                                Hubungkan dengan tim R&D kami untuk validasi formulasi akhir.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-orange-500"></span>
                                Gunakan tombol WhatsApp untuk konsultasi cepat dengan product specialist.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 h-2 w-2 rounded-full bg-orange-500"></span>
                                Export PDF/Word untuk dibagikan ke stakeholder internal.
                            </li>
                        </ul>
                        <a href="https://wa.me/6289510431269?text=Halo%20tim%20Skincare%20Simulator%2C%20saya%20butuh%20konsultasi%20untuk%20Simulasi%20ID%20{{ $simulation->id }}" class="btn-primary w-full justify-center" target="_blank">
                            Konsultasi via WhatsApp
                        </a>
                    </section>
                </div>
            </div>

            {{-- New Market Analysis Sections --}}
            <div class="space-y-6">
                {{-- Market Potential Section --}}
                <x-results.market-potential :result="$result" />
                
                {{-- Key Trends Section --}}
                <x-results.key-trends :result="$result" />
                
                {{-- Marketing Copywriting Section --}}
                <x-results.marketing-copywriting :result="$result" />
            </div>
        @else
            <x-results.processing-placeholder :simulation="$simulation" />
        @endif
    </div>
@endsection

@push('scripts')
<script>
// Data formatting helpers for market analysis
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

function formatPercentage(num) {
    return num.toFixed(1) + '%';
}

// Export simulation function
async function exportSimulation(format) {
    try {
        const response = await fetch(`/api/simulations/{{ $simulation->id }}/export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
            },
            body: JSON.stringify({
                format: format,
                sections: ['product_overview', 'ingredients', 'market_analysis', 'marketing_copywriting']
            })
        });

        const data = await response.json();

        if (data.success) {
            // Create download link
            const link = document.createElement('a');
            link.href = data.download_url;
            link.download = data.filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            showToast('Export berhasil! File akan segera didownload.', 'success');
        } else {
            showToast(data.message || 'Export gagal. Silakan coba lagi.', 'error');
        }
    } catch (error) {
        console.error('Export error:', error);
        showToast('Terjadi kesalahan saat export. Silakan coba lagi.', 'error');
    }
}

// Simple toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
        type === 'success' ? 'bg-emerald-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        document.body.removeChild(toast);
    }, 3000);
}
</script>
@endpush
