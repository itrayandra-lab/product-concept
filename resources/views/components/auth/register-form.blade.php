<div x-data="authFormComponent('register')" class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Buat akun baru</h1>
        <p class="mt-2 text-sm text-slate-500">Akses simulasi tanpa batas dan simpan riwayat produk Anda.</p>
    </div>

    <form class="space-y-4" x-on:submit.prevent="submit">
        <div>
            <label class="text-sm font-semibold text-slate-600">Nama Lengkap</label>
            <input type="text" class="input-field mt-1" x-model="form.name" required>
        </div>
        <div class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400" x-model="form.terms" id="terms">
            <label for="terms">
                Saya menyetujui <a href="#" class="font-semibold text-orange-600 hover:text-orange-500">Syarat & Ketentuan</a> serta kebijakan privasi.
            </label>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-600">Email</label>
            <input type="email" class="input-field mt-1" x-model="form.email" required>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-600">Password</label>
            <input type="password" class="input-field mt-1" x-model="form.password" minlength="8" required>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-600">Konfirmasi Password</label>
            <input type="password" class="input-field mt-1" x-model="form.password_confirmation" minlength="8" required>
        </div>

        <template x-if="errors.general">
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" x-text="errors.general[0]"></div>
        </template>

        <button type="submit" class="btn-primary w-full" x-bind:disabled="loading">
            <span x-show="!loading">Daftar & Mulai Simulasi</span>
            <span x-show="loading" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor" d="M12 2a10 10 0 00-10 10h4a6 6 0 016-6V2z" />
                </svg>
                Memproses...
            </span>
        </button>
    </form>

    <p class="text-center text-sm text-slate-500">
        Sudah memiliki akun?
        <a href="{{ url('/login') }}" class="font-semibold text-orange-600 hover:text-orange-500">Masuk di sini</a>
    </p>
</div>
