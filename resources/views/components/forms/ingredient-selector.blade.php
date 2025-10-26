<div class="card space-y-6">
    <div>
        <h3 class="section-title">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">3</span>
            Bahan Aktif & Konsentrasi
        </h3>
        <p class="mt-2 text-sm text-slate-500">Cari bahan di database atau masukkan manual untuk memastikan keamanan formulasi.</p>
    </div>

    <div class="relative">
        <input
            type="search"
            class="input-field pl-10"
            placeholder="Cari bahan (contoh: Niacinamide, Hyaluronic Acid, Retinol)"
            x-model.debounce.300ms="ingredientSearch"
            x-on:input.debounce.400ms="searchIngredient"
        >
        <svg class="pointer-events-none absolute left-3 top-3 h-5 w-5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.2-5.2M11 19a8 8 0 100-16 8 8 0 000 16z" />
        </svg>

        <div
            x-show="ingredientResults.length"
            x-transition
            class="absolute z-10 mt-2 w-full rounded-2xl border border-slate-100 bg-white p-3 shadow-lg"
        >
            <template x-for="ingredient in ingredientResults" :key="ingredient.id">
                <button
                    type="button"
                    class="flex w-full items-start justify-between rounded-xl px-3 py-2 text-left hover:bg-orange-50"
                    x-on:click.prevent="addIngredient(ingredient)"
                >
                    <div>
                        <p class="text-sm font-semibold text-slate-900" x-text="ingredient.name"></p>
                        <p class="text-xs text-slate-500" x-text="ingredient.inci_name"></p>
                    </div>
                    <span class="chip" x-text="ingredient.category?.name ?? 'Bahan Aktif'"></span>
                </button>
            </template>
        </div>
    </div>

    <div class="space-y-4">
        <template x-for="(ingredient, index) in formData.bahan_aktif" :key="'ingredient-' + index">
            <div class="rounded-2xl border border-slate-100 p-4">
                <div class="flex flex-col gap-4 md:flex-row md:items-center">
                    <div class="flex-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Bahan</label>
                        <input type="text" class="input-field mt-1" x-model="ingredient.name" placeholder="Nama bahan">
                    </div>
                    <div class="w-full md:w-40">
                        <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Konsentrasi</label>
                        <div class="mt-1 flex rounded-2xl border border-slate-200">
                            <input type="number" step="0.01" class="input-field border-0 flex-1 rounded-r-none" x-model="ingredient.concentration" placeholder="0.5">
                            <select class="input-field w-24 rounded-l-none border-0 border-l border-slate-100" x-model="ingredient.unit">
                                <template x-for="unit in ingredientUnits" :key="unit">
                                    <option :value="unit" x-text="unit"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary" x-on:click="removeIngredient(index)">Hapus</button>
                </div>
            </div>
        </template>
        <button
            type="button"
            class="btn-secondary w-full justify-center"
            x-show="!formData.bahan_aktif.length"
            x-on:click="formData.bahan_aktif.push({ name: '', concentration: '', unit: '%' })"
        >
            Tambah Bahan Manual
        </button>
    </div>

    <div
        x-show="compatibilityWarnings.length"
        x-transition
        class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"
    >
        <p class="font-semibold">Perhatian kompatibilitas:</p>
        <ul class="mt-2 list-disc space-y-1 pl-4">
            <template x-for="warning in compatibilityWarnings" :key="warning">
                <li x-text="warning"></li>
            </template>
        </ul>
    </div>
</div>
