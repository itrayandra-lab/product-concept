@props([
    'lookups' => [
        'productTypes' => [],
        'productFunctions' => [],
        'packagingTypes' => [],
        'targetAgeRanges' => [],
        'targetGenders' => [],
        'countries' => [],
        'finishingOptions' => [],
        'textureOptions' => [],
        'aromaOptions' => [],
        'certifications' => [],
        'claims' => [],
    ],
    'initialData' => [],
])

<div
    x-data="simulationFormComponent()"
    x-init="init()"
    class="space-y-8"
    x-cloak
    x-ref="formContainer"
>
    <!-- Safe data passing using script tag -->
    <script type="text/data" x-ref="initialData">@json($initialData)</script>
    <script type="text/data" x-ref="lookups">@json($lookups)</script>
    <header class="rounded-3xl bg-gradient-to-br from-blue-50 via-white to-white p-8 shadow-sm shadow-blue-500/10">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-500">Simulasi Produk</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Brief Produk Kosmetik</h1>
                <p class="mt-3 text-sm text-slate-600">Isi 18 parameter formulasi untuk mendapatkan rekomendasi AI yang lengkap.</p>
            </div>
            <div class="w-full max-w-sm rounded-2xl border border-blue-100 bg-white p-4">
                <p class="text-sm font-semibold text-slate-900">Progress Pengisian</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="h-2 flex-1 rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-blue-500 transition-all" :style="`width: ${progress}%`"></div>
                    </div>
                    <span class="text-sm font-semibold text-blue-600" x-text="`${progress}%`"></span>
                </div>
                <p class="mt-1 text-xs text-slate-500" x-text="$store.simulationForm.steps[$store.simulationForm.currentStep - 1]?.label"></p>
            </div>
        </div>
    </header>

    <nav class="grid gap-3 rounded-3xl bg-white p-4 shadow-sm shadow-blue-500/5 md:grid-cols-4">
        <template x-for="step in $store.simulationForm.steps" :key="step.id">
            <button
                type="button"
                class="flex flex-col rounded-2xl border px-4 py-3 text-left transition"
                :class="$store.simulationForm.currentStep === step.id ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-100 bg-white text-slate-600'"
                x-on:click="goToStep(step.id)"
            >
                <span class="text-xs font-semibold uppercase tracking-wide" x-text="`Langkah ${step.id}`"></span>
                <span class="text-sm font-semibold" x-text="step.label"></span>
            </button>
        </template>
    </nav>

    {{-- Step 1 --}}
    <section x-show="$store.simulationForm.currentStep === 1" x-transition>
        <div class="grid gap-6 md:grid-cols-2">
            <div class="card space-y-3">
                <label class="section-title mb-2">Nama Brand</label>
                <input type="text" class="input-field" x-model="formData.nama_brand" placeholder="Contoh: Luminary Beauty">
            </div>

            <div class="card space-y-3">
                <label class="section-title mb-2">Nama Produk</label>
                <input type="text" class="input-field" x-model="formData.nama_produk" placeholder="Contoh: HydraGlow Serum">
            </div>

            <div class="card space-y-4">
                <div>
                    <h3 class="section-title">Bentuk Formulasi</h3>
                    <p class="text-sm text-slate-500">Pilih tipe produk utama.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($lookups['productTypes'] as $type)
                        <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-3 text-sm font-medium hover:border-blue-200"
                            :class="formData.bentuk_formulasi === '{{ $type['name'] }}' ? 'border-blue-300 bg-blue-50 text-blue-600' : 'text-slate-700'">
                            <input type="radio" name="bentuk_formulasi" value="{{ $type['name'] }}" class="hidden" x-model="formData.bentuk_formulasi">
                            {{ $type['name'] }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card space-y-4">
                <div>
                    <h3 class="section-title">Fungsi Produk</h3>
                    <p class="text-sm text-slate-500">Pilih sampai 6 fungsi utama.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($lookups['productFunctions'] as $function)
                        <button
                            type="button"
                            class="rounded-2xl border px-4 py-2 text-sm font-medium transition"
                            :class="formData.fungsi_produk.includes('{{ $function['name'] }}') ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 text-slate-600'"
                            x-on:click="toggleArrayValue('fungsi_produk', '{{ $function['name'] }}')"
                        >
                            {{ $function['name'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="card space-y-3 md:col-span-2">
                <label class="section-title mb-2">Deskripsi Produk</label>
                <textarea class="input-field min-h-[160px]" x-model="formData.deskripsi_formula" placeholder="Jelaskan konsep produk, hero ingredient, dan manfaat utama..."></textarea>
            </div>

            <div class="card space-y-3">
                <label class="section-title mb-2">Benchmark Produk</label>
                <input type="text" class="input-field" x-model="formData.benchmark_product" placeholder="Opsional - contoh produk referensi">
            </div>
        </div>
    </section>

    {{-- Step 2 --}}
    <section x-show="$store.simulationForm.currentStep === 2" x-transition>
        <div class="grid gap-6 md:grid-cols-2">
            <div class="card space-y-4">
                <h3 class="section-title">Target Demografis</h3>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Gender</label>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @foreach ($lookups['targetGenders'] as $gender)
                            <label class="rounded-2xl border px-4 py-2 text-center text-sm font-medium"
                                :class="formData.target_gender === '{{ $gender['label'] }}' ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="target_gender" value="{{ $gender['label'] }}" class="hidden" x-model="formData.target_gender">
                                {{ $gender['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Rentang Usia</label>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($lookups['ageRanges'] as $age)
                            <button type="button" class="rounded-2xl border px-3 py-1 text-xs font-semibold"
                                :class="formData.target_usia.includes('{{ $age['label'] }}') ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 text-slate-600'"
                                x-on:click="toggleArrayValue('target_usia', '{{ $age['label'] }}')">
                                {{ $age['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card space-y-4">
                <h3 class="section-title">Parameter Pasar</h3>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Volume produk</label>
                    <div class="mt-1 flex gap-3">
                        <input type="number" min="0.1" step="0.1" class="input-field" x-model="formData.volume">
                        <select class="input-field w-32" x-model="formData.volume_unit">
                            @foreach (['ml', 'gram', 'oz', 'unit'] as $unit)
                                <option value="{{ $unit }}">{{ strtoupper($unit) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Step 3 --}}
    <section x-show="$store.simulationForm.currentStep === 3" x-transition>
        <x-forms.ingredient-selector />
    </section>

    {{-- Step 4 --}}
    <section x-show="$store.simulationForm.currentStep === 4" x-transition>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="section-title">Konfigurasi Lanjut</h3>
            <button type="button" class="btn-secondary w-full justify-center text-xs sm:w-auto" x-on:click="toggleAdvanced()" :aria-expanded="showAdvanced">
                <span x-show="showAdvanced">Sembunyikan Pengaturan</span>
                <span x-show="!showAdvanced">Tampilkan Pengaturan</span>
            </button>
        </div>
        <div x-show="showAdvanced" x-transition>
            <div class="grid gap-6 md:grid-cols-2">
            <div class="card space-y-4">
                <h3 class="section-title">Kemasan & Finishing</h3>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Tipe Kemasan</label>
                    <select class="input-field mt-1" x-model="formData.jenis_kemasan">
                        @foreach ($lookups['packagingTypes'] as $package)
                            <option value="{{ $package['name'] }}">{{ $package['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Finishing Kemasan</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($lookups['finishingOptions'] as $finish)
                            <button type="button" class="rounded-2xl border px-3 py-1 text-xs font-semibold"
                                :class="formData.finishing_kemasan === '{{ $finish }}' ? 'border-blue-200 bg-blue-50 text-blue-600' : 'border-slate-200 text-slate-600'"
                                x-on:click="formData.finishing_kemasan = '{{ $finish }}'">
                                {{ $finish }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Bahan Kemasan</label>
                    <input type="text" class="input-field mt-1" x-model="formData.bahan_kemasan" placeholder="Contoh: PCR Plastic, kaca, aluminium">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Kode Warna Produk</label>
                    <div class="mt-2 flex items-center gap-3">
                        <input type="color" class="h-12 w-24 cursor-pointer rounded-lg border-2 border-slate-200" x-model="formData.hex_color">
                        <input type="text" class="input-field flex-1 font-mono text-sm" x-model="formData.hex_color" placeholder="#FFFFFF" pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>
            </div>

            <div class="card space-y-4">
                <h3 class="section-title">Target Harga & Produksi</h3>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">Target Harga Produk</label>
                    <div class="mt-1 flex gap-2">
                        <select class="input-field w-28" x-model="formData.target_hpp_currency">
                            <option value="IDR">IDR</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                        <input type="number" class="input-field flex-1" x-model="formData.target_hpp" placeholder="10000">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-slate-400">MOQ (unit)</label>
                    <input type="number" class="input-field mt-1" x-model="formData.moq" placeholder="1000">
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase text-slate-400">Tekstur</label>
                        <select class="input-field mt-1" x-model="formData.tekstur">
                            <option value="">Pilih tekstur</option>
                            @foreach ($lookups['textureOptions'] as $texture)
                                <option value="{{ $texture }}">{{ $texture }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase text-slate-400">Aroma</label>
                        <select class="input-field mt-1" x-model="formData.aroma">
                            <option value="">Pilih aroma</option>
                            @foreach ($lookups['aromaOptions'] as $aroma)
                                <option value="{{ $aroma }}">{{ $aroma }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card space-y-4 md:col-span-2">
                <h3 class="section-title">Klaim & Sertifikasi</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase text-slate-400">Klaim Produk</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="text" class="input-field" placeholder="Opsional – tambahkan klaim baru" x-model="formData.klaim_produk_input" x-on:keydown.enter.prevent="
                                if (formData.klaim_produk_input) {
                                    formData.klaim_produk.push(formData.klaim_produk_input);
                                    formData.klaim_produk_input = '';
                                }
                            ">
                            <button type="button" class="btn-secondary" x-on:click="
                                if (formData.klaim_produk_input) {
                                    formData.klaim_produk.push(formData.klaim_produk_input);
                                    formData.klaim_produk_input = '';
                                }
                            ">Tambah</button>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="(claim, index) in formData.klaim_produk" :key="claim + index">
                                <span class="chip">
                                    <span x-text="claim"></span>
                                    <button type="button" class="ml-2 text-xs" x-on:click="formData.klaim_produk.splice(index, 1)">×</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold uppercase text-slate-400">Sertifikasi</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="text" class="input-field" placeholder="Halal, vegan, dermatologically tested"
                                x-model="formData.sertifikasi_input"
                                x-on:keydown.enter.prevent="
                                    if (formData.sertifikasi_input) {
                                        formData.sertifikasi.push(formData.sertifikasi_input);
                                        formData.sertifikasi_input = '';
                                    }
                                ">
                            <button type="button" class="btn-secondary" x-on:click="
                                if (formData.sertifikasi_input) {
                                    formData.sertifikasi.push(formData.sertifikasi_input);
                                    formData.sertifikasi_input = '';
                                }
                            ">Tambah</button>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="(cert, index) in formData.sertifikasi" :key="cert + index">
                                <span class="chip">
                                    <span x-text="cert"></span>
                                    <button type="button" class="ml-2 text-xs" x-on:click="formData.sertifikasi.splice(index, 1)">×</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="sticky bottom-4 flex flex-col gap-3 rounded-3xl border border-slate-100 bg-white/90 p-4 shadow-lg shadow-blue-500/10 md:flex-row md:items-center md:justify-between">
        <template x-if="Object.keys($store.simulationForm.errors).length">
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" role="alert" aria-live="assertive">
                <p class="font-semibold">Perlu diperbaiki:</p>
                <ul class="mt-1 list-disc space-y-1 pl-4">
                    <template x-for="(messages, field) in $store.simulationForm.errors" :key="field">
                        <li x-text="messages[0]"></li>
                    </template>
                </ul>
            </div>
        </template>
        <div class="flex items-center gap-3 text-sm text-slate-500" 
             x-show="autoSaveStatus && autoSaveStatus !== 'idle' && autoSaveStatus !== null" 
             aria-live="polite" 
             aria-atomic="true">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full"
                :class="{
                    'bg-emerald-100 text-emerald-600': autoSaveStatus === 'saved',
                    'bg-blue-100 text-blue-600': autoSaveStatus === 'saving',
                    'bg-rose-100 text-rose-600': autoSaveStatus === 'error'
                }">
                <template x-if="autoSaveStatus === 'saving'">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                    </svg>
                </template>
                <template x-if="autoSaveStatus && autoSaveStatus !== 'saving' && autoSaveStatus !== null">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </template>
            </span>
            <span x-text="autoSaveMessage || ''"></span>
        </div>
        <div class="flex flex-col gap-3 md:flex-row">
            <button type="button" class="btn-secondary" x-on:click="previousStep" x-bind:disabled="$store.simulationForm.currentStep === 1">Sebelumnya</button>
            <template x-if="$store.simulationForm.currentStep < $store.simulationForm.steps.length">
                <button type="button" class="btn-primary" x-on:click="nextStep">Lanjutkan</button>
            </template>
            <template x-if="$store.simulationForm.currentStep === $store.simulationForm.steps.length">
                <button type="button" class="btn-primary" x-on:click="submitForm" x-bind:disabled="$store.simulationForm.isSubmitting">
                    <span x-show="!$store.simulationForm.isSubmitting">Generate Simulasi</span>
                    <span x-show="$store.simulationForm.isSubmitting" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                            <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                        </svg>
                        Memproses...
                    </span>
                </button>
            </template>
        </div>
    </div>
</div>
