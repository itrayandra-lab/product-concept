@extends('layouts.app', ['title' => 'Riwayat Simulasi'])

@section('content')
    <section class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Riwayat Simulasi</h1>
            <p class="text-sm text-slate-500">Pantau progres konsep produk Anda dan lanjutkan riset kapan saja.</p>
        </div>

        @auth
            @if ($simulations->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white/80 p-8 text-center">
                    <p class="text-sm text-slate-500">Belum ada simulasi tersimpan. Mulai dengan mengisi brief produk.</p>
                    <a href="{{ route('simulator') }}" class="btn-primary mt-4 inline-flex">Mulai Simulasi Baru</a>
                </div>
            @else
                <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Dibuat</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                            @foreach ($simulations as $simulation)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-900">
                                            {{ data_get($simulation->output_data, 'product_name', 'Konsep Produk') }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            ID #{{ $simulation->id }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="chip">
                                            {{ ucfirst($simulation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ optional($simulation->created_at)->format('d M Y · H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('simulations.results', $simulation) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">
                                            Lihat hasil
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @else
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white/80 p-8 text-center">
                <p class="text-sm text-slate-500">Masuk untuk melihat riwayat simulasi dan men-download hasil sebelumnya.</p>
                <div class="mt-4 flex justify-center gap-3">
                    <a href="{{ route('login') }}" class="btn-secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary">Daftar Gratis</a>
                </div>
            </div>
        @endauth
    </section>
@endsection
