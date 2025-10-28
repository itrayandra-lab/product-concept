<div x-data="authFormComponent('login')" class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Masuk ke akun</h1>
        <p class="mt-2 text-sm text-slate-500">Gunakan email dan password yang sudah terdaftar.</p>
    </div>

    <button type="button" class="btn-secondary w-full justify-center" x-on:click="window.location.href='/auth/google'">
        <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.5-.2-2.2H12v4.1h6.5c-.3 1.4-1.1 2.6-2.3 3.4v2.8h3.8c2.2-2 3.5-5 3.5-8.1z"/>
            <path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-3l-3.8-2.8c-1.1.7-2.5 1.1-4.1 1.1-3.2 0-5.9-2.1-6.8-5H1.3v3.1C3.3 21.8 7.3 24 12 24z"/>
            <path fill="#FBBC05" d="M5.2 14.3c-.2-.7-.4-1.4-.4-2.3s.1-1.6.4-2.3V6.6H1.3C.5 8.2.1 9.9.1 12s.4 3.8 1.2 5.4l3.9-3.1z"/>
            <path fill="#EA4335" d="M12 4.7c1.7 0 3.2.6 4.4 1.9l3.3-3.3C17.9 1.2 15.2 0 12 0 7.3 0 3.3 2.2 1.3 5.7l3.9 3.1c.9-2.8 3.6-4.1 6.8-4.1z"/>
        </svg>
        Masuk dengan Google
    </button>

    <div class="relative text-center text-xs uppercase tracking-[0.3em] text-slate-400">
        <span class="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 bg-slate-100"></span>
        <span class="relative bg-white px-3">atau</span>
    </div>

    <form class="space-y-4" x-on:submit.prevent="submit">
        <div>
            <label class="text-sm font-semibold text-slate-600">Email</label>
            <input type="email" class="input-field mt-1" x-model="form.email" required>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-600">Password</label>
            <input type="password" class="input-field mt-1" x-model="form.password" required minlength="8">
        </div>
        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-slate-600">
                <input type="checkbox" class="rounded" x-model="form.remember">
                Ingat saya
            </label>
            <a href="{{ url('/forgot-password') }}" class="font-semibold text-orange-600 hover:text-orange-500">Lupa password?</a>
        </div>

        <template x-if="errors.general">
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700" x-text="errors.general[0]"></div>
        </template>

        <button type="submit" class="btn-primary w-full" x-bind:disabled="loading">
            <span x-show="!loading">Masuk</span>
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
        Belum punya akun?
        <a href="{{ url('/register') }}" class="font-semibold text-orange-600 hover:text-orange-500">Daftar sekarang</a>
    </p>
</div>
