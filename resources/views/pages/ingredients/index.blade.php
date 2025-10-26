@extends('layouts.app', ['title' => 'Database Bahan'])

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Database Bahan Aktif</h1>
            <p class="text-sm text-slate-500">Sumber cepat untuk mencari inspirasi formulasi sebelum menjalankan simulasi.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($ingredients as $ingredient)
                <article class="card space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $ingredient->name }}</h2>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">{{ $ingredient->inci_name }}</p>
                        </div>
                        <span class="chip">{{ optional($ingredient->category)->name ?? 'Bahan Aktif' }}</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ $ingredient->effects }}</p>
                    <p class="text-xs text-slate-400">Catatan keamanan: {{ $ingredient->safety_notes ?? 'Aman digunakan sesuai batas regulasi.' }}</p>
                </article>
            @empty
                <p class="rounded-3xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">Belum ada data bahan.</p>
            @endforelse
        </div>
    </div>
@endsection
