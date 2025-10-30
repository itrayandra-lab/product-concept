import '../css/app.css';
import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const clone = (value) => JSON.parse(JSON.stringify(value));
const randomId = () =>
    (typeof crypto !== 'undefined' && crypto.randomUUID
        ? crypto.randomUUID()
        : `toast_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`);

const MAX_FUNCTIONS = 6;
const MAX_INGREDIENTS = 10;
const MIN_DESCRIPTION_LENGTH = 50;

const simulationSteps = [
    { id: 1, key: 'basic', label: 'Detail Produk' },
    { id: 2, key: 'market', label: 'Target Pasar' },
    { id: 3, key: 'ingredients', label: 'Komposisi' },
    { id: 4, key: 'advanced', label: 'Konfigurasi Lanjut' },
];

const defaultFormData = {
    nama_brand: '',
    nama_produk: '',
    fungsi_produk: [],
    bentuk_formulasi: '',
    target_gender: 'Semua Gender',
    target_usia: [],
    target_negara: 'Indonesia',
    deskripsi_formula: '',
    benchmark_product: '',
    volume: 30,
    volume_unit: 'ml',
    hex_color: '#FFFFFF',
    jenis_kemasan: 'Airless Pump',
    finishing_kemasan: '',
    bahan_kemasan: '',
    target_hpp: '',
    target_hpp_currency: 'IDR',
    moq: '',
    tekstur: '',
    aroma: '',
    klaim_produk: [],
    klaim_produk_input: '',
    sertifikasi: [],
    sertifikasi_input: '',
    bahan_aktif: [],
};

const ingredientCompatibilityRules = [
    {
        keywords: ['retinol'],
        conflicts: ['vitamin c', 'ascorbic', 'aha', 'bha', 'glycolic', 'lactic', 'salicylic'],
        message: 'Retinol sebaiknya tidak digabung dengan Vitamin C/AHA/BHA dalam satu formula high-dosage.',
    },
    {
        keywords: ['niacinamide'],
        conflicts: ['vitamin c', 'ascorbic'],
        message: 'Niacinamide + Vitamin C dapat meningkatkan risiko iritasi pada kulit sensitif.',
    },
];

const createSimulationStore = () => ({
    currentStep: 1,
    steps: simulationSteps,
    formData: clone(defaultFormData),
    errors: {},
    isSubmitting: false,

    bootstrap(initialData = {}) {
        this.formData = Object.assign(clone(defaultFormData), initialData);
        this.errors = {};
        this.currentStep = 1;
    },

    setErrors(errors = {}) {
        this.errors = errors;
    },

    clearErrors() {
        this.errors = {};
    },

    completionPercentage() {
        const requiredFields = [
            'fungsi_produk',
            'bentuk_formulasi',
            'target_gender',
            'target_usia',
            'deskripsi_formula',
            'bahan_aktif',
            'volume',
            'volume_unit',
        ];

        const filled = requiredFields.filter((field) => {
            const value = this.formData[field];
            if (Array.isArray(value)) {
                return value.length > 0;
            }

            return !!value;
        }).length;

        return Math.round((filled / requiredFields.length) * 100);
    },
});

const createUiStore = () => ({
    isGlobalLoading: false,
    toasts: [],
    toast(message, variant = 'success') {
        const id = randomId();
        this.toasts.push({ id, message, variant });
        setTimeout(() => {
            this.toasts = this.toasts.filter((toast) => toast.id !== id);
        }, 4000);
    },
});

const createExportStore = () => ({
    isExporting: false,
    async export(simulationId, format = 'pdf') {
        if (!simulationId) return;
        this.isExporting = true;
        try {
            const response = await fetch(`/api/simulations/${simulationId}/export`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ format }),
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message ?? 'Gagal mengekspor hasil');
            }

            window.location.href = payload.data.download_url;
            Alpine.store('ui').toast('Dokumen siap diunduh');
        } catch (error) {
            console.error(error);
            Alpine.store('ui').toast(error.message, 'error');
        } finally {
            this.isExporting = false;
        }
    },
});

Alpine.store('ui', createUiStore());
Alpine.store('simulationForm', createSimulationStore());
Alpine.store('export', createExportStore());

window.triggerGuestSessionSimulation = async function triggerGuestSessionSimulation(sessionId = null) {
    const guestSessionId = sessionId ?? localStorage.getItem('guest_session_id');
    if (!guestSessionId) {
        return null;
    }

    const token = localStorage.getItem('auth_token');
    if (!token) {
        return null;
    }

    const response = await fetch('/api/simulations/generate-from-guest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({ guest_session_id: guestSessionId }),
    });

    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.message ?? 'Gagal memulihkan data guest');
    }

    localStorage.removeItem('guest_session_id');
    localStorage.removeItem(`form_data_${guestSessionId}`);
    localStorage.removeItem(`form_saved_at_${guestSessionId}`);

    return payload.data?.simulation_id ?? payload.data?.id ?? null;
};

window.simulationFormComponent = function simulationFormComponent() {
    return {
        store: null,
        formData: {},
        ingredientSearch: '',
        ingredientResults: [],
        ingredientLoading: false,
        ingredientUnits: ['%', 'mg', 'g', 'ml', 'ppm'],
        autoSaveStatus: 'idle',
        autoSaveMessage: '',
        guestSessionId: localStorage.getItem('guest_session_id') || null,
        completedSteps: [],
        progress: 0,
        compatibilityWarnings: [],
        showAdvanced: true,
        lookups: {},
        isSubmitting: false,

        init() {
            // Initialize store first
            this.store = Alpine.store('simulationForm');
            
            // Safe data parsing with error handling
            try {
                // Parse initial data from script tag
                const initialDataElement = this.$refs.initialData;
                const lookupsElement = this.$refs.lookups;
                
                let initialData = {};
                let lookups = {};
                
                if (initialDataElement && initialDataElement.textContent) {
                    initialData = JSON.parse(initialDataElement.textContent);
                }
                
                if (lookupsElement && lookupsElement.textContent) {
                    lookups = JSON.parse(lookupsElement.textContent);
                }
                
                this.lookups = lookups;
                this.store.bootstrap(initialData);
                this.formData = this.store.formData;
                this.progress = this.store.completionPercentage();
                this.updateCompletedSteps();
                this.updateCompatibilityWarnings();
                this.restoreGuestSession();
                this.checkPostAuthRestore();
                
            } catch (error) {
                console.error('Error parsing Alpine.js data:', error);
                // Fallback to default initialization
                if (this.store) {
                    this.store.bootstrap({});
                    this.formData = this.store.formData;
                    this.progress = this.store.completionPercentage();
                    this.updateCompletedSteps();
                    this.updateCompatibilityWarnings();
                    this.restoreGuestSession();
                    this.checkPostAuthRestore();
                }
            }

            // Add delay to ensure DOM is ready
            this.$nextTick(() => {
                if (this.store && this.formData) {
                    this.$watch(
                        'formData',
                        () => {
                            try {
                                if (this.store && this.store.completionPercentage) {
                                    this.progress = this.store.completionPercentage();
                                    this.updateCompletedSteps();
                                    this.updateCompatibilityWarnings();
                                    this.debouncedSave();
                                }
                            } catch (error) {
                                console.error('Error in formData watcher:', error);
                            }
                        },
                        { deep: true },
                    );
                }
            });

            window.addEventListener('beforeunload', (event) => {
                if (this.autoSaveStatus === 'saving' && !this.isSubmitting) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        },

        toggleAdvanced() {
            this.showAdvanced = !this.showAdvanced;
        },

        checkPostAuthRestore() {
            const params = new URLSearchParams(window.location.search);
            const sessionFromQuery = params.get('guest_session');
            if (!sessionFromQuery) {
                return;
            }

            window
                .triggerGuestSessionSimulation(sessionFromQuery)
                .then((simulationId) => {
                    if (simulationId) {
                        Alpine.store('ui').toast('Form dipulihkan, membuka hasil simulasi');
                        window.location.href = `/simulations/${simulationId}/results`;
                    }
                })
                .catch((error) => {
                    console.error('[GuestRestore]', error);
                    Alpine.store('ui').toast(error.message, 'error');
                })
                .finally(() => {
                    params.delete('guest_session');
                    const nextQuery = params.toString();
                    const newUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}`;
                    window.history.replaceState({}, '', newUrl);
                });
        },

        goToStep(stepId) {
            if (stepId > this.store.currentStep) {
                if (!this.validateStep()) {
                    return;
                }
            }

            this.store.currentStep = stepId;
            this.store.clearErrors();
        },

        nextStep() {
            if (!this.validateStep()) {
                return;
            }

            if (this.store.currentStep < this.store.steps.length) {
                this.store.currentStep += 1;
                this.store.clearErrors();
            }
        },

        previousStep() {
            if (this.store.currentStep > 1) {
                this.store.currentStep -= 1;
                this.store.clearErrors();
            }
        },

        toggleArrayValue(field, value) {
            const current = this.formData[field] ?? [];
            if (current.includes(value)) {
                this.formData[field] = current.filter((item) => item !== value);
                return;
            }

            if (field === 'fungsi_produk' && current.length >= MAX_FUNCTIONS) {
                Alpine.store('ui').toast(`Maksimal ${MAX_FUNCTIONS} fungsi produk`, 'warning');
                return;
            }

            this.formData[field] = [...current, value];
        },

        addIngredient(ingredient) {
            if (!ingredient?.name) return;

            if (this.formData.bahan_aktif.length >= MAX_INGREDIENTS) {
                Alpine.store('ui').toast(`Maksimal ${MAX_INGREDIENTS} bahan aktif`, 'warning');
                return;
            }

            const exists = this.formData.bahan_aktif.some(
                (item) => item.name.toLowerCase() === ingredient.name.toLowerCase(),
            );
            if (exists) {
                Alpine.store('ui').toast('Bahan sudah ditambahkan', 'warning');
                return;
            }

            this.formData.bahan_aktif.push({
                name: ingredient.name,
                inci_name: ingredient.inci_name ?? ingredient.name,
                concentration: ingredient.concentration ?? '',
                unit: ingredient.unit ?? '%',
            });
            this.ingredientResults = [];
            this.ingredientSearch = '';
            this.updateCompatibilityWarnings();
        },

        removeIngredient(index) {
            this.formData.bahan_aktif.splice(index, 1);
            this.updateCompatibilityWarnings();
        },

        async searchIngredient() {
            if (this.ingredientSearch.length < 2) {
                this.ingredientResults = [];
                return;
            }
            this.ingredientLoading = true;
            try {
                const response = await fetch(
                    `/api/ingredients?search=${encodeURIComponent(this.ingredientSearch)}&per_page=5`,
                );
                const payload = await response.json();
                this.ingredientResults = payload.data ?? [];
            } catch (error) {
                console.error('[IngredientSearch]', error);
            } finally {
                this.ingredientLoading = false;
            }
        },

        updateCompletedSteps() {
            this.completedSteps = simulationSteps
                .filter((step) => this.isStepComplete(step.key))
                .map((step) => step.key);
        },

        isStepComplete(stepKey) {
            switch (stepKey) {
                case 'basic':
                    return Boolean(
                        this.formData.bentuk_formulasi &&
                            this.formData.fungsi_produk.length &&
                            this.formData.deskripsi_formula?.trim().length >= MIN_DESCRIPTION_LENGTH,
                    );
                case 'market':
                    return Boolean(
                        this.formData.target_gender &&
                            this.formData.target_usia.length &&
                            this.formData.volume,
                    );
                case 'ingredients':
                    return this.formData.bahan_aktif.length > 0;
                case 'advanced':
                    return Boolean(
                        this.formData.jenis_kemasan ||
                            this.formData.finishing_kemasan ||
                            this.formData.target_hpp ||
                            this.formData.klaim_produk.length ||
                            this.formData.sertifikasi.length,
                    );
                default:
                    return false;
            }
        },

        validateStep(stepKey = this.store.steps[this.store.currentStep - 1]?.key, persist = true) {
            if (!stepKey) return true;

            const errors = {};
            const addError = (field, message) => {
                errors[field] = errors[field] || [];
                errors[field].push(message);
            };

            switch (stepKey) {
                case 'basic':
                    if (!this.formData.bentuk_formulasi) addError('bentuk_formulasi', 'Pilih bentuk formulasi.');
                    if (!this.formData.fungsi_produk.length) {
                        addError('fungsi_produk', 'Pilih minimal satu fungsi produk.');
                    }
                    if (this.formData.deskripsi_formula.trim().length < MIN_DESCRIPTION_LENGTH) {
                        addError('deskripsi_formula', `Deskripsi minimal ${MIN_DESCRIPTION_LENGTH} karakter.`);
                    }
                    break;
                case 'market':
                    if (!this.formData.target_gender) addError('target_gender', 'Pilih target gender.');
                    if (!this.formData.target_usia.length) addError('target_usia', 'Pilih minimal satu rentang usia.');
                    if (!this.formData.volume || Number(this.formData.volume) <= 0) {
                        addError('volume', 'Masukkan volume produk yang valid.');
                    }
                    break;
                case 'ingredients':
                    if (!this.formData.bahan_aktif.length) addError('bahan_aktif', 'Tambahkan minimal satu bahan aktif.');
                    this.formData.bahan_aktif.forEach((ingredient, index) => {
                        if (!ingredient.name?.trim()) {
                            addError(`bahan_aktif.${index}.name`, `Nama bahan ke-${index + 1} wajib diisi.`);
                        }
                        if (ingredient.concentration && Number(ingredient.concentration) <= 0) {
                            addError(
                                `bahan_aktif.${index}.concentration`,
                                `Konsentrasi bahan ke-${index + 1} harus lebih besar dari 0.`,
                            );
                        }
                    });
                    break;
                case 'advanced':
                    if (!this.formData.jenis_kemasan) addError('jenis_kemasan', 'Pilih tipe kemasan.');
                    break;
                default:
                    break;
            }

            if (persist) {
                this.store.setErrors(errors);
            }

            return Object.keys(errors).length === 0;
        },

        validateAllSteps() {
            for (const step of this.store.steps) {
                if (!this.validateStep(step.key, true)) {
                    this.store.currentStep = step.id;
                    return false;
                }
            }
            this.store.clearErrors();
            return true;
        },

        updateCompatibilityWarnings() {
            const names = this.formData.bahan_aktif
                .map((item) => (item.name || '').toLowerCase())
                .filter((name) => !!name);
            const warnings = [];

            ingredientCompatibilityRules.forEach((rule) => {
                const hasKeyword = rule.keywords.some((keyword) => names.some((name) => name.includes(keyword)));
                const hasConflict = rule.conflicts.some((conflict) => names.some((name) => name.includes(conflict)));
                if (hasKeyword && hasConflict) {
                    warnings.push(rule.message);
                }
            });

            this.compatibilityWarnings = warnings;
        },

        async submitForm() {
            if (!this.validateAllSteps()) {
                Alpine.store('ui').toast('Lengkapi field wajib sebelum generate simulasi.', 'warning');
                return;
            }

            this.isSubmitting = true;
            this.store.isSubmitting = true;
            this.store.clearErrors();
            try {
                const response = await fetch('/api/simulations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'X-Guest-Session': this.guestSessionId ?? '',
                    },
                    body: JSON.stringify(this.formData),
                });

                const payload = await response.json();
                if (!response.ok) {
                    // Handle authentication required
                    if (response.status === 401 && payload.auth_required) {
                        this.showAuthRequired();
                        return;
                    }
                    
                    if (payload.errors) {
                        this.store.setErrors(payload.errors);
                    }
                    throw new Error(payload.message ?? 'Gagal mengirim simulasi');
                }

                Alpine.store('ui').toast('Simulasi diproses. Mengarahkan ke hasil...');
                const simulationId = payload.data?.id ?? payload.data?.simulation_id;
                if (simulationId) {
                    window.location.href = `/simulations/${simulationId}/results`;
                }
            } catch (error) {
                Alpine.store('ui').toast(error.message, 'error');
                console.error('[SimulationForm]', error);
            } finally {
                this.isSubmitting = false;
                this.store.isSubmitting = false;
            }
        },

        showAuthRequired() {
            // Set submitting flag to prevent beforeunload dialog
            this.isSubmitting = true;
            
            // Save current form data before redirecting to auth
            this.saveGuestSession();
            
            // Show auth modal or redirect to login
            Alpine.store('ui').toast('Login diperlukan untuk generate simulasi', 'info');
            
            // Redirect to login with guest session parameter
            const guestSessionId = this.guestSessionId || localStorage.getItem('guest_session_id');
            if (guestSessionId) {
                window.location.href = `/login?guest_session=${guestSessionId}`;
            } else {
                window.location.href = '/login';
            }
        },

        restoreGuestSession() {
            if (!this.guestSessionId) return;
            try {
                const savedData = localStorage.getItem(`form_data_${this.guestSessionId}`);
                if (savedData) {
                    const parsed = JSON.parse(savedData);
                    this.formData = Object.assign(this.formData, parsed);
                }
            } catch (error) {
                console.warn('[GuestSession] Failed to restore local data', error);
            }
        },

        debouncedSave() {
            if (this.formData && Object.keys(this.formData).length > 0) {
                clearTimeout(this._saveTimeout);
                this._saveTimeout = setTimeout(() => this.saveGuestSession(), 2000);
            }
        },

        async saveGuestSession() {
            this.autoSaveStatus = 'saving';
            this.autoSaveMessage = 'Menyimpan draf...';
            const sessionId =
                this.guestSessionId || `guest_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
            try {
                localStorage.setItem(`form_data_${sessionId}`, JSON.stringify(this.formData));
                localStorage.setItem('guest_session_id', sessionId);
                this.guestSessionId = sessionId;

                const response = await fetch('/api/guest/save-form-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Guest-Session': sessionId,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        form_data: this.formData,
                        form_step: simulationSteps[this.store.currentStep - 1]?.key,
                        completed_steps: this.completedSteps,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Gagal menyimpan sesi');
                }

                this.autoSaveStatus = 'saved';
                this.autoSaveMessage = `Tersimpan otomatis ${new Date().toLocaleTimeString()}`;
                setTimeout(() => {
                    if (this.autoSaveStatus === 'saved') {
                        this.autoSaveStatus = 'idle';
                        this.autoSaveMessage = '';
                    }
                }, 3000);
            } catch (error) {
                console.error('[GuestSession] Save failed', error);
                this.autoSaveStatus = 'error';
                this.autoSaveMessage = 'Gagal menyimpan, perubahan disimpan lokal';
                Alpine.store('ui').toast('Gagal menyimpan ke server, data tersimpan lokal', 'error');
            }
        },
    };
};

window.resultViewerComponent = function resultViewerComponent(simulationId) {
    return {
        simulationId,
        data: null,
        loading: true,
        error: null,

        init() {
            if (!this.simulationId) {
                this.error = 'ID simulasi tidak valid';
                this.loading = false;
                return;
            }
            this.fetchResult();
        },

        async fetchResult() {
            this.loading = true;
            this.error = null;
            Alpine.store('ui').isGlobalLoading = true;
            try {
                const response = await fetch(`/api/simulations/${this.simulationId}`);
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message ?? 'Gagal memuat hasil simulasi');
                }
                this.data = payload.data ?? payload;
            } catch (error) {
                console.error('[ResultViewer]', error);
                this.error = error.message;
            } finally {
                this.loading = false;
                Alpine.store('ui').isGlobalLoading = false;
            }
        },
    };
};

window.resultStatusTracker = function resultStatusTracker(simulationId, initialStatus = 'processing') {
    return {
        simulationId,
        status: initialStatus,
        progress: 0,
        error: null,
        isLoading: false,
        intervalId: null,

        init() {
            if (this.status !== 'completed') {
                this.startPolling();
            }
        },

        startPolling() {
            if (this.intervalId) return;
            this.intervalId = setInterval(() => {
                this.fetchStatus();
            }, 5000);
            this.fetchStatus();
        },

        stopPolling() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },

        friendlyStatus() {
            switch ((this.status || '').toLowerCase()) {
                case 'completed':
                    return 'Selesai';
                case 'failed':
                    return 'Gagal';
                default:
                    return 'Sedang Diproses';
            }
        },

        statusDescription() {
            switch ((this.status || '').toLowerCase()) {
                case 'completed':
                    return 'Simulasi telah selesai diproses.';
                case 'failed':
                    return 'Terjadi kendala saat memproses simulasi. Silakan coba regenerasi.';
                default:
                    return 'AI sedang menyiapkan hasil lengkap Anda. Halaman akan diperbarui otomatis.';
            }
        },

        async fetchStatus() {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await fetch(`/api/simulations/${this.simulationId}/status`);
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error(payload.message ?? 'Status tidak tersedia');
                }

                const nextStatus = payload.data?.status ?? payload.status ?? this.status;
                const progressValue = payload.data?.progress ?? payload.progress ?? null;

                this.status = nextStatus;
                this.progress = progressValue ?? this.progress;

                if (this.status === 'completed') {
                    this.stopPolling();
                    Alpine.store('ui').toast('Simulasi selesai! Memuat hasil...');
                    setTimeout(() => window.location.reload(), 1200);
                }
            } catch (error) {
                console.error('[ResultStatus]', error);
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },
    };
};

window.authFormComponent = function authFormComponent(mode = 'login') {
    return {
        mode,
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            remember: true,
            terms: false,
        },
        loading: false,
        errors: {},
        successMessage: '',

        async submit() {
            this.loading = true;
            this.errors = {};
            this.successMessage = '';
            let endpoint = '/api/auth/login';
            const payload = {
                email: this.form.email,
                password: this.form.password,
                remember: this.form.remember,
            };

            if (this.mode === 'register') {
                endpoint = '/api/auth/register';
                Object.assign(payload, {
                    name: this.form.name,
                    password_confirmation: this.form.password_confirmation,
                });
            }

            if (this.mode === 'forgot') {
                endpoint = '/api/auth/forgot-password';
                Object.assign(payload, { email: this.form.email });
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();
                if (!response.ok) {
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                    throw new Error(data.message ?? 'Terjadi kesalahan');
                }

                if (this.mode === 'forgot') {
                    this.successMessage = data.message ?? 'Email reset sudah dikirim.';
                    return;
                }

                const token = data.token ?? data.data?.access_token ?? data.data?.token ?? '';
                localStorage.setItem('auth_token', token);

                try {
                    const simulationId = await window.triggerGuestSessionSimulation();
                    if (simulationId) {
                        Alpine.store('ui').toast('Form guest dipulihkan, menampilkan hasil');
                        window.location.href = `/simulations/${simulationId}/results`;
                        return;
                    }
                } catch (guestError) {
                    console.error('[AuthForm][GuestRestore]', guestError);
                }

                Alpine.store('ui').toast(data.message ?? 'Berhasil masuk');
                window.location.href = '/simulator';
            } catch (error) {
                console.error('[AuthForm]', error);
                this.errors.general = [error.message];
            } finally {
                this.loading = false;
            }
        },
    };
};

window.guestFormAutoSave = window.guestFormAutoSave || function guestFormAutoSave() {
    return simulationFormComponent();
};

Alpine.start();
