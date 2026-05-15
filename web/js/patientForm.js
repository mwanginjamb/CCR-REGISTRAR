/**
 * patientForm.js - Offline-first form handler with dynamic UI
 */

document.addEventListener('DOMContentLoaded', () => {
    const API_ENDPOINT = '/patient-api/create';
    const SYNC_TAG = 'sync-patient-records';
    const form = document.getElementById('patient-form');
    const statusBanner = document.getElementById('sync-status-banner');
    const submitBtn = document.getElementById('submit-form');
    const offlineSaveBtn = document.getElementById('offline-save-btn');
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const modeIndicator = document.getElementById('mode-indicator');
    const syncQueueIndicator = document.getElementById('sync-queue-indicator');
    const syncQueueCount = document.getElementById('sync-queue-count');

    if (!form) {
        console.warn('Patient form not found');
        return;
    }

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Flag to prevent multiple submissions
    let isSubmitting = false;
    let pendingSyncCount = 0;

    // ── UI Mode Management ──────────────────────────────────────────────────
    function updateUIMode() {
        const isOnline = navigator.onLine;

        if (isOnline) {
            // Online Mode
            document.body.classList.remove('offline-mode');
            document.body.classList.add('online-mode');

            // Update mode indicator
            if (modeIndicator) {
                modeIndicator.textContent = 'LIVE MODE';
                modeIndicator.className = 'px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-success-container text-on-success-container';
            }

            // Show/hide buttons
            if (submitBtn) submitBtn.style.display = 'flex';
            if (offlineSaveBtn) offlineSaveBtn.style.display = 'none';

            // Update draft button text
            const draftText = document.getElementById('save-draft-text');
            if (draftText) draftText.textContent = 'Save as Draft';

            // Check for pending syncs when coming online
            checkPendingSyncs();

        } else {
            // Offline Mode
            document.body.classList.remove('online-mode');
            document.body.classList.add('offline-mode');

            // Update mode indicator
            if (modeIndicator) {
                modeIndicator.textContent = 'DRAFT MODE (OFFLINE)';
                modeIndicator.className = 'px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-warning-container text-on-warning-container';
            }

            // Show/hide buttons
            if (submitBtn) submitBtn.style.display = 'none';
            if (offlineSaveBtn) offlineSaveBtn.style.display = 'flex';

            // Update draft button text for offline
            const draftText = document.getElementById('save-draft-text');
            if (draftText) draftText.textContent = 'Save Draft Locally';
        }

        console.log(`UI Mode updated: ${isOnline ? 'ONLINE' : 'OFFLINE'}`);
    }

    // ── Check Pending Syncs in IndexedDB ──────────────────────────────────────
    async function checkPendingSyncs() {
        try {
            const pending = await PatientDB.getPending();
            pendingSyncCount = pending.length;

            if (syncQueueCount) {
                syncQueueCount.textContent = pendingSyncCount;
            }

            if (syncQueueIndicator && pendingSyncCount > 0) {
                syncQueueIndicator.style.display = 'block';
                syncQueueIndicator.classList.add('sync-pending');

                // If online and have pending syncs, trigger sync
                if (navigator.onLine && pendingSyncCount > 0) {
                    showBanner(`${pendingSyncCount} pending syncs found. Syncing...`, 'info');
                    triggerBackgroundSync();
                }
            } else if (syncQueueIndicator && pendingSyncCount === 0) {
                syncQueueIndicator.style.display = 'none';
                syncQueueIndicator.classList.remove('sync-pending');
            }
        } catch (err) {
            console.error('Failed to check pending syncs:', err);
        }
    }

    // ── Trigger Background Sync ───────────────────────────────────────────────
    async function triggerBackgroundSync() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register(SYNC_TAG);
                showBanner('Syncing pending records...', 'info');
            } catch (err) {
                console.error('Background sync registration failed:', err);
            }
        }
    }

    // ── Register Service Worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.warn('SW registration failed:', err));

        navigator.serviceWorker.addEventListener('message', ({ data }) => {
            if (data?.type === 'SYNC_COMPLETE') {
                showBanner('Patient record synced successfully!', 'success');
                checkPendingSyncs(); // Update queue count

                if (data.patient_id && !window.location.pathname.includes(`/patient/${data.patient_id}`)) {
                    setTimeout(() => {
                        window.location.href = `/patient/${data.patient_id}`;
                    }, 1500);
                }
            }
            if (data?.type === 'SYNC_FAILED') {
                showBanner('Sync failed — will retry automatically when online', 'warning');
                checkPendingSyncs();
            }
            if (data?.type === 'SYNC_VALIDATION_ERROR') {
                showBanner('Record was rejected by server — please check data', 'error');
                checkPendingSyncs();
            }
        });
    }

    // ── Online / Offline Event Handlers ──────────────────────────────────────
    const updateOnlineUI = () => {
        updateUIMode();

        // Show additional notification when coming online
        if (navigator.onLine) {
            showBanner('Back online! Syncing pending records...', 'success');
            triggerBackgroundSync();
        } else {
            showBanner('You are offline. Data will be saved locally and synced when online.', 'warning');
        }
    };

    window.addEventListener('online', updateOnlineUI);
    window.addEventListener('offline', updateOnlineUI);

    // Initial UI setup
    updateUIMode();
    checkPendingSyncs(); // Check for any pending syncs on load

    // ── Main Form Submit Handler (Online) ─────────────────────────────────────
    const handleFormSubmit = async (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Only allow online submission when online
        if (!navigator.onLine) {
            showBanner('You are offline. Please use "Save Offline" button.', 'warning');
            return;
        }

        if (isSubmitting) {
            console.log('Form already submitting, please wait...');
            return false;
        }

        isSubmitting = true;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">hourglass_empty</span>Saving...</span>';
        }

        try {
            const coords = GeoTag.getCoords ? GeoTag.getCoords() : null;
            const formData = serializeForm(form);

            if (coords && coords.lat && coords.lng) {
                formData._geo = {
                    lat: coords.lat,
                    lng: coords.lng,
                    accuracy: coords.accuracy || 0,
                    captured_at: coords.captured_at || new Date().toISOString().replace('T', ' ').replace(/\.\d+/, '')
                };
            }

            formData._csrf = csrfToken;
            const payload = buildApiPayload(formData);

            // Save to IndexedDB first
            let localId;
            try {
                localId = await PatientDB.save({
                    sync_status: 'pending',
                    server_id: null,
                    form_data: payload,
                    created_at: Date.now()
                });
                showBanner('Saved locally, syncing to server...', 'info');
            } catch (dbErr) {
                console.error('IndexedDB save failed:', dbErr);
                showBanner('Failed to save locally!', 'error');
                return;
            }

            // Sync immediately
            await attemptSync(localId, payload);

        } catch (error) {
            console.error('Form submission error:', error);
            showBanner('An error occurred. Please try again.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">cloud_upload</span>Submit Online</span>';
            }
            isSubmitting = false;
        }

        return false;
    };

    // ── Offline Save Handler ─────────────────────────────────────────────────
    const handleOfflineSave = async (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (isSubmitting) return;
        isSubmitting = true;

        if (offlineSaveBtn) {
            offlineSaveBtn.disabled = true;
            offlineSaveBtn.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">hourglass_empty</span>Saving...</span>';
        }

        try {
            const coords = GeoTag.getCoords ? GeoTag.getCoords() : null;
            const formData = serializeForm(form);

            if (coords && coords.lat && coords.lng) {
                formData._geo = coords;
            }
            formData._csrf = csrfToken;
            formData._offline_save = true;

            const payload = buildApiPayload(formData);

            await PatientDB.save({
                sync_status: 'pending',
                server_id: null,
                form_data: payload,
                is_offline_save: true,
                created_at: Date.now()
            });

            showBanner('Saved offline! Will sync when online.', 'success');

            // Update pending count
            await checkPendingSyncs();

            // Optional: Clear form after offline save
            setTimeout(() => {
                if (confirm('Form saved offline. Would you like to clear the form and start a new entry?')) {
                    form.reset();
                }
            }, 2000);

        } catch (dbErr) {
            console.error('Offline save failed:', dbErr);
            showBanner('Failed to save offline!', 'error');
        } finally {
            if (offlineSaveBtn) {
                offlineSaveBtn.disabled = false;
                offlineSaveBtn.innerHTML = '<span class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">offline_bolt</span>Save Offline</span>';
            }
            isSubmitting = false;
        }
    };

    // ── Save as Draft Handler ────────────────────────────────────────────────
    const handleSaveDraft = async (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        showBanner('Saving draft locally...', 'info');

        try {
            const coords = GeoTag.getCoords ? GeoTag.getCoords() : null;
            const formData = serializeForm(form);

            if (coords && coords.lat && coords.lng) {
                formData._geo = coords;
            }
            formData._csrf = csrfToken;
            formData._draft = true;

            await PatientDB.save({
                sync_status: 'pending',
                server_id: null,
                form_data: formData,
                is_draft: true,
                created_at: Date.now()
            });

            showBanner('Draft saved locally', 'success');
            await checkPendingSyncs();

        } catch (dbErr) {
            console.error('Draft save failed:', dbErr);
            showBanner('Failed to save draft', 'error');
        }
    };

    // ── Build API Payload (same as before) ────────────────────────────────────
    function buildApiPayload(formData) {
        const payload = {
            Patient: {},
            Tumour: {},
            Treatment: [],
            Sources: {},
            FollowUp: {}
        };

        Object.keys(formData).forEach(key => {
            if (key.startsWith('Patient')) {
                const fieldName = key.replace('Patient', '').toLowerCase();
                payload.Patient[fieldName] = formData[key];
            } else if (key.startsWith('Tumour')) {
                const fieldName = key.replace('Tumour', '').toLowerCase();
                payload.Tumour[fieldName] = formData[key];
            } else if (key.startsWith('Treatment')) {
                const match = key.match(/Treatment\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const idx = parseInt(match[1]);
                    const field = match[2];
                    if (!payload.Treatment[idx]) payload.Treatment[idx] = {};
                    payload.Treatment[idx][field] = formData[key];
                }
            } else if (key.startsWith('Sources')) {
                const fieldName = key.replace('Sources', '').toLowerCase();
                payload.Sources[fieldName] = formData[key];
            } else if (key.startsWith('FollowUp')) {
                const fieldName = key.replace('FollowUp', '').toLowerCase();
                payload.FollowUp[fieldName] = formData[key];
            } else if (key === 'concurrent_illness') {
                payload.concurrent_illness = formData[key];
            } else if (key === '_geo') {
                payload._geo = formData[key];
            }
        });

        if (Object.keys(payload.Patient).length === 0) delete payload.Patient;
        if (Object.keys(payload.Tumour).length === 0) delete payload.Tumour;
        if (payload.Treatment.length === 0) delete payload.Treatment;
        if (Object.keys(payload.Sources).length === 0) delete payload.Sources;
        if (Object.keys(payload.FollowUp).length === 0) delete payload.FollowUp;

        return payload;
    }

    // ── Sync with Server ──────────────────────────────────────────────────────
    async function attemptSync(localId, payload) {
        try {
            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(`HTTP ${response.status}: ${JSON.stringify(errorData)}`);
            }

            const result = await response.json();
            await PatientDB.markSynced(localId, result.id);
            showBanner('Patient record saved and synced!', 'success');

            await checkPendingSyncs(); // Update queue

            if (result.id) {
                setTimeout(() => {
                    window.location.href = `/patient/${result.id}`;
                }, 1200);
            }

        } catch (err) {
            console.warn('Sync failed, queuing background sync:', err);
            showBanner('Saved offline — will sync in background', 'warning');
            await triggerBackgroundSync();
        }
    }

    // ── Serialize Form Data ───────────────────────────────────────────────────
    function serializeForm(formElement) {
        const data = {};
        const formData = new FormData(formElement);

        for (let [key, value] of formData.entries()) {
            const matches = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
            if (matches) {
                const [, model, index, field] = matches;
                const arrayKey = `${model}Array`;
                if (!data[arrayKey]) data[arrayKey] = [];
                if (!data[arrayKey][index]) data[arrayKey][index] = {};
                data[arrayKey][index][field] = value;
            } else {
                if (key === 'geo_captured' && value) {
                    value = value.replace('T', ' ').replace(/\.\d+Z?$/, '').trim();
                }
                data[key] = value;
            }
        }

        if (data.TreatmentArray) {
            data.Treatment = data.TreatmentArray.filter(t => t && Object.keys(t).length > 0);
            delete data.TreatmentArray;
        }

        if (data.geo_lat && data.geo_lng) {
            data._geo = {
                lat: parseFloat(data.geo_lat),
                lng: parseFloat(data.geo_lng),
                accuracy: data.geo_accuracy ? parseFloat(data.geo_accuracy) : 0,
                captured_at: data.geo_captured || new Date().toISOString().replace('T', ' ').replace(/\.\d+/, '')
            };
        }

        return data;
    }

    // ── Show Banner Message ───────────────────────────────────────────────────
    function showBanner(msg, type) {
        if (!statusBanner) return;

        const colours = {
            success: 'bg-tertiary-container text-on-tertiary-container',
            error: 'bg-error-container text-on-error-container',
            warning: 'bg-warning-container text-on-warning-container',
            info: 'bg-surface-container text-on-surface-variant'
        };

        statusBanner.textContent = msg;
        statusBanner.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl font-semibold shadow-lg transition-all ${colours[type] || colours.info}`;
        statusBanner.style.display = 'block';

        if (statusBanner._timer) clearTimeout(statusBanner._timer);
        statusBanner._timer = setTimeout(() => {
            statusBanner.style.display = 'none';
        }, 5000);
    }

    // ── Attach Event Listeners ────────────────────────────────────────────────
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (navigator.onLine) {
            handleFormSubmit(e);
        } else {
            handleOfflineSave(e);
        }
    });

    if (submitBtn) {
        submitBtn.addEventListener('click', handleFormSubmit);
    }

    if (offlineSaveBtn) {
        offlineSaveBtn.addEventListener('click', handleOfflineSave);
    }

    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', handleSaveDraft);
    }

    // Back button functionality
    const backBtn = document.getElementById('back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.history.back();
        });
    }

    console.log('Patient form initialized - Dynamic UI mode active');

});