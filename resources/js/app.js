import './bootstrap';

/**
 * Guest Form Auto-Save Component
 * Automatically saves guest user form data to localStorage and backend
 */
window.guestFormAutoSave = function() {
    return {
        // Form data (18 fields)
        formData: {
            product_name: '',
            target_demographic: '',
            skin_type: '',
            skin_concerns: [],
            ingredients: [],
            product_type: '',
            packaging_type: '',
            price_range: '',
            brand_positioning: '',
            marketing_message: '',
            target_market: '',
            regulatory_requirements: '',
            sustainability_goals: '',
            innovation_focus: '',
            budget_constraints: '',
            timeline: '',
            success_metrics: '',
            competitive_analysis: ''
        },
        
        // Auto-save state
        guestSessionId: localStorage.getItem('guest_session_id') || null,
        lastSaved: null,
        autoSaveEnabled: true,
        saveStatus: 'idle', // idle, saving, saved, error
        saveMessage: '',
        formProgress: 0,
        completedSteps: [],
        formStep: 'basic',
        retryCount: 0,
        maxRetries: 3,
        
        // Initialize
        init() {
            console.log('[GuestAutoSave] Initializing...');
            
            // Load existing form data
            this.loadSavedFormData();
            
            // Watch form data changes (debounced)
            this.$watch('formData', () => {
                if (this.autoSaveEnabled) {
                    this.debouncedSave();
                }
            }, { deep: true });
            
            // Save before page unload
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges()) {
                    this.saveFormData(false); // Synchronous save
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            
            console.log('[GuestAutoSave] Initialized successfully');
        },
        
        // Load previously saved form data
        loadSavedFormData() {
            if (this.guestSessionId) {
                const savedData = localStorage.getItem(`form_data_${this.guestSessionId}`);
                if (savedData) {
                    try {
                        const parsed = JSON.parse(savedData);
                        this.formData = { ...this.formData, ...parsed };
                        this.lastSaved = localStorage.getItem(`form_saved_at_${this.guestSessionId}`);
                        console.log('[GuestAutoSave] Loaded saved data from localStorage');
                    } catch (error) {
                        console.error('[GuestAutoSave] Failed to load saved data:', error);
                    }
                }
            }
        },
        
        // Debounced save function (wait 2 seconds after last change)
        debouncedSave() {
            clearTimeout(this._saveTimeout);
            this._saveTimeout = setTimeout(() => {
                this.saveFormData();
            }, 2000);
        },
        
        // Save form data to localStorage and backend
        async saveFormData(async = true) {
            this.saveStatus = 'saving';
            this.saveMessage = 'Saving...';
            
            try {
                // Generate session ID if not exists
                const sessionId = this.guestSessionId || `guest_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
                
                // Save to localStorage first (immediate)
                try {
                    const dataToSave = { ...this.formData };
                    localStorage.setItem(`form_data_${sessionId}`, JSON.stringify(dataToSave));
                    localStorage.setItem(`form_saved_at_${sessionId}`, new Date().toISOString());
                    localStorage.setItem('guest_session_id', sessionId);
                    this.guestSessionId = sessionId;
                } catch (storageError) {
                    console.warn('[GuestAutoSave] localStorage save failed:', storageError);
                }
                
                // Save to backend (async)
                if (async) {
                    const response = await fetch('/api/guest/save-form-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Guest-Session': sessionId,
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            form_data: this.formData,
                            form_step: this.formStep,
                            completed_steps: this.completedSteps
                        })
                    });
                    
                    if (response.ok) {
                        const result = await response.json();
                        this.guestSessionId = result.data.guest_session_id;
                        this.formProgress = result.data.form_progress;
                        this.completedSteps = result.data.completed_steps || [];
                        this.lastSaved = new Date().toISOString();
                        this.saveStatus = 'saved';
                        this.saveMessage = `Saved at ${new Date().toLocaleTimeString()}`;
                        this.retryCount = 0;
                        
                        // Auto-hide status after 2 seconds
                        setTimeout(() => {
                            if (this.saveStatus === 'saved') {
                                this.saveStatus = 'idle';
                                this.saveMessage = '';
                            }
                        }, 2000);
                        
                        console.log('[GuestAutoSave] Saved to backend successfully');
                    } else {
                        throw new Error(`Backend save failed: ${response.statusText}`);
                    }
                } else {
                    // Synchronous mode (before unload)
                    this.saveStatus = 'saved';
                    console.log('[GuestAutoSave] Saved to localStorage (sync mode)');
                }
            } catch (error) {
                console.error('[GuestAutoSave] Save failed:', error);
                this.saveStatus = 'error';
                this.saveMessage = 'Failed to save. Retrying...';
                
                // Retry logic
                if (this.retryCount < this.maxRetries) {
                    this.retryCount++;
                    setTimeout(() => {
                        console.log(`[GuestAutoSave] Retrying... (${this.retryCount}/${this.maxRetries})`);
                        this.saveFormData();
                    }, 2000 * this.retryCount); // Exponential backoff
                } else {
                    this.saveMessage = 'Error saving data. Changes saved locally.';
                    setTimeout(() => {
                        if (this.saveStatus === 'error') {
                            this.saveStatus = 'idle';
                            this.saveMessage = '';
                        }
                    }, 5000);
                }
            }
        },
        
        // Check if there are unsaved changes
        hasUnsavedChanges() {
            if (!this.lastSaved) return true;
            
            // Compare current data with saved data
            const savedData = localStorage.getItem(`form_data_${this.guestSessionId}`);
            if (!savedData) return true;
            
            try {
                const parsed = JSON.parse(savedData);
                return JSON.stringify(parsed) !== JSON.stringify(this.formData);
            } catch {
                return true;
            }
        },
        
        // Update form step
        setFormStep(step) {
            this.formStep = step;
            this.saveFormData();
        },
        
        // Get progress percentage
        getProgress() {
            return this.formProgress;
        },
        
        // Check if step is completed
        isStepCompleted(step) {
            return this.completedSteps.includes(step);
        }
    };
};
