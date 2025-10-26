@props(['simulation'])

<section class="space-y-6">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="card animate-pulse space-y-4">
                <div class="h-5 w-2/5 rounded-full bg-slate-100"></div>
                <div class="h-4 w-full rounded-full bg-slate-100"></div>
                <div class="h-4 w-5/6 rounded-full bg-slate-100"></div>
                <div class="h-4 w-2/3 rounded-full bg-slate-100"></div>
            </div>
            <div class="card animate-pulse space-y-3">
                <div class="h-4 w-1/4 rounded-full bg-slate-100"></div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="h-20 rounded-2xl bg-slate-100"></div>
                    @endfor
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="card animate-pulse">
                <div class="h-4 w-1/3 rounded-full bg-slate-100"></div>
                <div class="mt-4 space-y-3">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="h-3 rounded-full bg-slate-100"></div>
                    @endfor
                </div>
            </div>
            <div class="card">
                <p class="text-sm text-slate-500">
                    Simulasi #{{ $simulation->id }} masih berjalan. Kami akan memberi notifikasi otomatis saat hasil siap.
                </p>
                <p class="mt-4 text-xs text-slate-400">Jika membutuhkan bantuan cepat, hubungi tim kami melalui WhatsApp.</p>
            </div>
        </div>
    </div>
</section>
