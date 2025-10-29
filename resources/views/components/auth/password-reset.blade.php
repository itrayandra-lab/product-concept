<div x-data="authFormComponent('forgot')" class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Reset password</h1>
        <p class="mt-2 text-sm text-slate-500">Masukkan email Anda untuk menerima tautan reset password.</p>
    </div>

    <form class="space-y-4" x-on:submit.prevent="submit">
        <div>
            <label class="text-sm font-semibold text-slate-600">Email</label>
            <input type="email" class="input-field mt-1" x-model="form.email" required>
        </div>

        <template x-if="errors.general">
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" x-text="errors.general[0]"></div>
        </template>

        <template x-if="successMessage">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-700" x-text="successMessage"></div>
        </template>

        <button type="submit" class="btn-primary w-full" x-bind:disabled="loading">
            <span x-show="!loading">Kirim tautan reset</span>
            <span x-show="loading" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                </svg>
                Mengirim...
            </span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-500">
        Sudah ingat password?
        <a href="{{ url('/login') }}" class="font-semibold text-blue-600 hover:text-blue-500">Kembali ke login</a>
    </p>
</div>
