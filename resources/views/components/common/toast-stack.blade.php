<div
    x-data
    class="pointer-events-none fixed inset-x-0 bottom-4 z-[60] flex flex-col items-center gap-3 sm:items-end sm:px-6 no-print"
>
    <template x-for="toast in ($store.ui && $store.ui.toasts) || []" :key="toast.id">
        <div
            x-show="toast && toast.id"
            x-transition.opacity
            class="pointer-events-auto w-full max-w-sm rounded-2xl border px-4 py-3 shadow-lg shadow-slate-900/10 sm:w-80"
            :class="toast && toast.variant === 'error'
                ? 'border-rose-200 bg-rose-50 text-rose-700'
                : toast && toast.variant === 'warning'
                    ? 'border-amber-200 bg-amber-50 text-amber-800'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-800'"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
        >
            <div class="flex items-start gap-3">
                <span class="text-lg">
                    <template x-if="toast && toast.variant === 'error'">⚠️</template>
                    <template x-if="toast && toast.variant === 'warning'">⚡</template>
                    <template x-if="toast && toast.variant === 'success'">✅</template>
                </span>
                <p class="text-sm font-semibold" x-text="toast && toast.message ? toast.message : ''"></p>
            </div>
        </div>
    </template>
</div>
