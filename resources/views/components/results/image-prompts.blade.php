@props(['result' => null])

@php
    $imagePrompts = data_get($result, 'image_prompts', []);
    
    // Debug: Check if image_prompts exists
    $hasImagePrompts = !empty($imagePrompts);
    
    $hero = data_get($imagePrompts, 'product_hero_shot');
    $lifestyles = data_get($imagePrompts, 'lifestyle_shots', []);
    $ingredients = data_get($imagePrompts, 'ingredient_visualization', []);
    $beforeAfter = data_get($imagePrompts, 'before_after_mockup');
    $socials = data_get($imagePrompts, 'social_media_assets', []);
    $packaging = data_get($imagePrompts, 'packaging_mockup');

    // Build a single big string for bulk copy/export
    $bulkLines = [];
    if ($hero) {
        $bulkLines[] = "# Product Hero Shot\n" . $hero;
    }
    if (!empty($lifestyles)) {
        foreach ($lifestyles as $idx => $txt) {
            $bulkLines[] = "# Lifestyle Shot " . ($idx + 1) . "\n" . $txt;
        }
    }
    if (!empty($ingredients)) {
        foreach ($ingredients as $idx => $txt) {
            $bulkLines[] = "# Ingredient Visualization " . ($idx + 1) . "\n" . $txt;
        }
    }
    if ($beforeAfter) {
        $bulkLines[] = "# Before / After Mockup\n" . $beforeAfter;
    }
    if (!empty($socials)) {
        foreach ($socials as $obj) {
            $platform = strtoupper(data_get($obj, 'platform', 'SOCIAL'));
            $bulkLines[] = "# Social Media (" . $platform . ")\n" . data_get($obj, 'prompt', '');
        }
    }
    if ($packaging) {
        $bulkLines[] = "# Packaging Mockup\n" . $packaging;
    }
    $bulkText = implode("\n\n", $bulkLines);
@endphp

<section class="card space-y-4" x-data="imagePrompts()">
    <div class="flex items-center justify-between gap-4">
        <h3 class="section-title">AI Image Generation Prompts</h3>
        @if ($hasImagePrompts)
        <div class="flex gap-2 no-print">
            <button type="button" class="btn-secondary" @click="copyBulk($refs.bulk.value)">Copy All</button>
            <button type="button" class="btn-primary" @click="exportTxt($refs.bulk.value)">Export .txt</button>
        </div>
        @endif
    </div>

    @if (!$hasImagePrompts)
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
        <div class="flex gap-3">
            <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-amber-800">Image Prompts Tidak Tersedia</h4>
                <p class="mt-1 text-sm text-amber-700">
                    Hasil simulasi ini dibuat sebelum fitur Image Prompts ditambahkan. Silakan buat simulasi baru untuk mendapatkan AI image generation prompts.
                </p>
            </div>
        </div>
    </div>
    @else
    <p class="text-sm text-slate-600">Gunakan prompt di bawah ini pada Midjourney, DALL-E, atau Stable Diffusion untuk menghasilkan aset visual. Klik copy untuk menyalin.</p>

    <textarea x-ref="bulk" class="hidden">{{ $bulkText }}</textarea>

    @if ($hasImagePrompts)
    <div class="divide-y divide-slate-200 rounded-xl border border-slate-200">
        {{-- Product Hero Shot --}}
        <div class="p-4" x-data="{ open: true }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Product Hero Shot</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-3">
                @if ($hero)
                    <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ $hero }}</pre>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary" @click="copy(`{{ addslashes($hero) }}`)">Copy</button>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endif
            </div>
        </div>

        {{-- Lifestyle Shots --}}
        <div class="p-4" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Lifestyle Shots</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-4">
                @forelse ($lifestyles as $idx => $txt)
                    <div class="space-y-2">
                        <div class="text-sm font-medium text-slate-700">Scene {{ $idx + 1 }}</div>
                        <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ $txt }}</pre>
                        <button type="button" class="btn-secondary" @click="copy(`{{ addslashes($txt) }}`)">Copy</button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endforelse
            </div>
        </div>

        {{-- Ingredient Visualization --}}
        <div class="p-4" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Ingredient Visualization</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-4">
                @forelse ($ingredients as $idx => $txt)
                    <div class="space-y-2">
                        <div class="text-sm font-medium text-slate-700">Visualization {{ $idx + 1 }}</div>
                        <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ $txt }}</pre>
                        <button type="button" class="btn-secondary" @click="copy(`{{ addslashes($txt) }}`)">Copy</button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endforelse
            </div>
        </div>

        {{-- Before / After --}}
        <div class="p-4" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Before / After Mockup</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-3">
                @if ($beforeAfter)
                    <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ $beforeAfter }}</pre>
                    <button type="button" class="btn-secondary" @click="copy(`{{ addslashes($beforeAfter) }}`)">Copy</button>
                @else
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endif
            </div>
        </div>

        {{-- Social Media Assets --}}
        <div class="p-4" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Social Media Assets</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-4">
                @forelse ($socials as $obj)
                    <div class="space-y-2">
                        <div class="text-sm font-medium text-slate-700">{{ ucfirst(data_get($obj, 'platform', 'platform')) }} ({{ data_get($obj, 'dimensions', '') }})</div>
                        <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ data_get($obj, 'prompt', '') }}</pre>
                        <button type="button" class="btn-secondary" @click="copy(`{{ addslashes(data_get($obj, 'prompt', '')) }}`)">Copy</button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endforelse
            </div>
        </div>

        {{-- Packaging Mockup --}}
        <div class="p-4" x-data="{ open: false }">
            <button type="button" class="flex w-full items-center justify-between" @click="open = !open">
                <span class="font-medium text-slate-800">Packaging Mockup</span>
                <svg :class="{'rotate-180': open}" class="h-4 w-4 transform transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-3">
                @if ($packaging)
                    <pre class="whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-800">{{ $packaging }}</pre>
                    <button type="button" class="btn-secondary" @click="copy(`{{ addslashes($packaging) }}`)">Copy</button>
                @else
                    <p class="text-sm text-slate-500">Tidak ada prompt tersedia.</p>
                @endif
            </div>
        </div>
    </div>
    @endif
    @endif

    <template x-if="toast.show">
        <div class="fixed bottom-6 right-6 z-50 rounded-lg bg-slate-900 px-4 py-2 text-sm text-white shadow-lg" x-text="toast.message"></div>
    </template>
</section>

@once
@push('scripts')
<script>
function imagePrompts() {
    return {
        toast: { show: false, message: '' },
        notify(message) {
            this.toast.message = message;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false }, 1500);
        },
        async copy(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.notify('Prompt disalin');
            } catch (e) {
                this.notify('Gagal menyalin');
            }
        },
        async copyBulk(text) {
            return this.copy(text);
        },
        exportTxt(text) {
            const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'image-prompts.txt';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
            this.notify('Berhasil export .txt');
        }
    }
}
</script>
@endpush
@endonce


