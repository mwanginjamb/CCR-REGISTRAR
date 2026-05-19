/**
 * patientForm.js - Offline-first form handler with dynamic UI and sync fallbacks
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

    // Flags
    let isSubmitting = false;
    let pendingSyncCount = 0;
    let syncRetryCount = 0;
    let pollingInterval = null;
    const MAX_SYNC_RETRIES = 5;
    const POLLING_INTERVAL = 30000; // 30 seconds

    // ── Helper: Check if Background Sync is supported ─────────────────────────
    async function isBackgroundSyncSupported() {
        if (!('serviceWorker' in navigator)) {
            console.log('Service Worker not supported');
            return false;
        }

        if (!('SyncManager' in window)) {
            console.log('Background Sync API not supported');
            return false;
        }

        // Check if we're on HTTPS or localhost
        const isSecure = location.protocol === 'https:' ||
            location.hostname === 'localhost' ||
            location.hostname === '127.0.0.1';

        if (!isSecure) {
            console.warn('Background Sync requires HTTPS (except localhost)');
            return false;
        }

        // Check if Service Worker is active
        try {
            const registration = await navigator.serviceWorker.ready;
            if (!registration.active) {
                console.log('Service Worker not active yet');
                return false;
            }
            return true;
        } catch (err) {
            console.warn('Service Worker not ready:', err);
            return false;
        }
    }

    // ── Direct Sync Fallback (doesn't require Background Sync API) ────────────
    async function directSyncFallback() {
        console.log('Attempting direct sync fallback...');
        if (!navigator.onLine) return false;

        try {
            const pending = await PatientDB.getPending();
            if (pending.length === 0) return true;

            showBanner(`Syncing ${pending.length} record(s)...`, 'info');
            let synced = 0;
            let failed = 0;
            let detailedErrors = [];

            for (const record of pending) {
                const result = await syncRecordWithRetry(record);
                if (result.success) {
                    synced++;
                } else {
                    failed++;
                    if (result.errorDetail) detailedErrors.push(result.errorDetail);
                }
            }

            if (synced > 0) showBanner(`Synced ${synced} record(s) successfully!`, 'success');
            if (failed > 0) {
                const msg = `${failed} record(s) failed to sync. Check details.`;
                showBanner(msg, 'warning');
                if (detailedErrors.length) showDetailedErrors(detailedErrors);
            }
            await checkPendingSyncs();
            return synced > 0;
        } catch (err) {
            console.error('Direct sync fallback failed:', err);
            return false;
        }
    }

    async function syncRecordWithRetry(record, retriesLeft = 3) {
        const maxRetries = 3;
        let currentRetry = 0;
        let delay = 1000; // initial backoff
        console.log('Syncing record payload:', JSON.stringify(record.form_data, null, 2));
        while (currentRetry <= maxRetries) {
            try {
                const response = await fetch(API_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken || ''
                    },
                    body: JSON.stringify(record.form_data)
                });

                if (response.ok) {
                    const result = await response.json();
                    await PatientDB.markSynced(record.local_id, result.id);
                    console.log(`✅ Sync success for record ${record.local_id}`);
                    return { success: true };
                }

                // Handle HTTP errors
                const errorData = await response.json().catch(() => ({}));
                const isTransient = response.status >= 500 || response.status === 429;
                const errorMsg = `HTTP ${response.status}: ${JSON.stringify(errorData)}`;
                console.error(`❌ Sync failed for ${record.local_id}: ${errorMsg}`);

                if (response.status === 422) {
                    // Permanent validation error
                    await PatientDB.markError(record.local_id, errorMsg, response.status, errorData);
                    return {
                        success: false,
                        errorDetail: {
                            local_id: record.local_id,
                            status: response.status,
                            message: errorMsg,
                            details: errorData
                        }
                    };
                }

                if (isTransient && currentRetry < maxRetries) {
                    // Retry with backoff
                    console.log(`Retrying ${record.local_id} in ${delay}ms (attempt ${currentRetry + 1}/${maxRetries})...`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2; // exponential backoff
                    currentRetry++;
                    continue;
                }

                // Permanent or retries exhausted
                await PatientDB.markError(record.local_id, errorMsg, response.status, errorData);
                return {
                    success: false,
                    errorDetail: {
                        local_id: record.local_id,
                        status: response.status,
                        message: errorMsg,
                        details: errorData
                    }
                };
            } catch (err) {
                // Network error or fetch exception
                console.error(`Network error for ${record.local_id}:`, err);
                if (currentRetry < maxRetries) {
                    console.log(`Retrying ${record.local_id} in ${delay}ms (attempt ${currentRetry + 1}/${maxRetries})...`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2;
                    currentRetry++;
                    continue;
                }
                await PatientDB.markError(record.local_id, err.message, null, null);
                return {
                    success: false,
                    errorDetail: {
                        local_id: record.local_id,
                        status: 'network',
                        message: err.message
                    }
                };
            }
        }
        return { success: false };
    }

    function showDetailedErrors(errors) {
        // Create a modal or expandable section
        let modal = document.getElementById('sync-error-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'sync-error-modal';
            modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden';
            modal.innerHTML = `
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[80vh] overflow-auto p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-red-600">Sync Errors</h3>
                    <button id="close-error-modal" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>
                <div id="error-details-list" class="space-y-3"></div>
                <div class="mt-4 flex justify-end gap-2">
                    <button id="retry-all-errors" class="px-4 py-2 bg-primary text-white rounded">Retry All</button>
                    <button id="close-error-modal-btn" class="px-4 py-2 bg-gray-300 rounded">Close</button>
                </div>
            </div>
        `;
            document.body.appendChild(modal);
            document.getElementById('close-error-modal')?.addEventListener('click', () => modal.classList.add('hidden'));
            document.getElementById('close-error-modal-btn')?.addEventListener('click', () => modal.classList.add('hidden'));
            document.getElementById('retry-all-errors')?.addEventListener('click', async () => {
                modal.classList.add('hidden');
                await retryFailedRecords();
            });
        }

        const listContainer = document.getElementById('error-details-list');
        listContainer.innerHTML = '';
        errors.forEach(err => {
            const div = document.createElement('div');
            div.className = 'border-l-4 border-red-500 bg-red-50 p-3 rounded';
            div.innerHTML = `
            <div class="font-mono text-sm">Record #${err.local_id}</div>
            <div class="text-xs text-gray-600">Status: ${err.status}</div>
            <div class="text-xs break-all">${err.message}</div>
            ${err.details && err.details.errors ? `<details class="mt-2"><summary class="cursor-pointer text-xs">Details</summary><pre class="text-xs bg-gray-100 p-2 rounded overflow-auto">${JSON.stringify(err.details.errors, null, 2)}</pre></details>` : ''}
        `;
            listContainer.appendChild(div);
        });
        modal.classList.remove('hidden');
    }

    async function retryFailedRecords() {
        showBanner('Retrying failed records...', 'info');
        try {
            const errors = await PatientDB.getErrors();
            for (const rec of errors) {
                await PatientDB.resetToPending(rec.local_id);
            }
            await directSyncFallback();
        } catch (err) {
            console.error('Retry failed:', err);
            showBanner('Failed to retry records', 'error');
        }
    }

    // Expose retry function globally
    window.retryFailedRecords = retryFailedRecords;

    // ── Trigger Background Sync with fallback ─────────────────────────────────
    async function triggerBackgroundSync() {
        if (!navigator.onLine) {
            console.log('Cannot sync while offline');
            return false;
        }

        const syncSupported = await isBackgroundSyncSupported();

        if (!syncSupported) {
            console.log('Background sync not available, using direct sync');
            return await directSyncFallback();
        }

        try {
            // Wait for SW to be ready
            console.log('Waiting for Service Worker to be ready...');
            const registration = await navigator.serviceWorker.ready;

            if (!registration.active) {
                console.log('SW not active yet, waiting for activation...');

                // Wait for SW activation with timeout
                await new Promise((resolve, reject) => {
                    const timeout = setTimeout(() => reject(new Error('SW activation timeout')), 10000);

                    if (registration.installing) {
                        registration.installing.addEventListener('statechange', (e) => {
                            if (e.target.state === 'activated') {
                                clearTimeout(timeout);
                                resolve();
                            }
                        });
                    } else if (registration.waiting) {
                        registration.waiting.postMessage({ type: 'SKIP_WAITING' });
                        registration.waiting.addEventListener('statechange', (e) => {
                            if (e.target.state === 'activated') {
                                clearTimeout(timeout);
                                resolve();
                            }
                        });
                    } else {
                        clearTimeout(timeout);
                        resolve();
                    }
                });
            }

            // Try to register sync
            console.log('Registering background sync...');
            await registration.sync.register(SYNC_TAG);
            console.log('Background sync registered successfully');
            showBanner('Background sync scheduled', 'info');
            return true;

        } catch (err) {
            console.error('Background sync registration failed:', err);

            // Fallback to direct sync on certain errors
            if (err.name === 'NotAllowedError') {
                console.log('Sync permission denied, falling back to direct sync');
                return await directSyncFallback();
            } else if (syncRetryCount < MAX_SYNC_RETRIES) {
                syncRetryCount++;
                const delay = 5000 * syncRetryCount;
                console.log(`Retrying sync in ${delay / 1000}s (${syncRetryCount}/${MAX_SYNC_RETRIES})...`);
                setTimeout(() => triggerBackgroundSync(), delay);
                return false;
            } else {
                console.log('Max retries reached, using direct sync fallback');
                return await directSyncFallback();
            }
        }
    }

    // ── Setup Polling Sync (fallback for browsers without Background Sync) ────
    function setupPollingSync() {
        // Clear existing interval
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }

        // Only set up polling if Background Sync is not available
        (async () => {
            const syncAvailable = await isBackgroundSyncSupported();
            if (syncAvailable) {
                console.log('Background Sync available, skipping polling fallback');
                return;
            }

            console.log('Setting up polling sync fallback (every 30 seconds)');

            // Poll when online
            const poll = () => {
                if (navigator.onLine) {
                    console.log('Polling sync check...');
                    directSyncFallback();
                }
            };

            // Start polling
            pollingInterval = setInterval(poll, POLLING_INTERVAL);

            // Also sync when coming online
            window.addEventListener('online', () => {
                console.log('Online detected, syncing immediately...');
                directSyncFallback();
            });

            // Initial sync after delay
            setTimeout(poll, 5000);
        })();
    }

    // ── Cleanup polling on page unload ────────────────────────────────────────
    window.addEventListener('beforeunload', () => {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });

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

                // Make indicator clickable for manual sync
                syncQueueIndicator.style.cursor = 'pointer';
                syncQueueIndicator.onclick = async () => {
                    showBanner('Manual sync triggered...', 'info');
                    await triggerBackgroundSync();
                };

                // Show tooltip on hover
                syncQueueIndicator.title = `${pendingSyncCount} pending sync(s) - Click to sync now`;

                // If online and have pending syncs, trigger sync
                if (navigator.onLine && pendingSyncCount > 0) {
                    showBanner(`${pendingSyncCount} pending syncs found. Syncing...`, 'info');
                    await triggerBackgroundSync();
                }
            } else if (syncQueueIndicator && pendingSyncCount === 0) {
                syncQueueIndicator.style.display = 'none';
                syncQueueIndicator.classList.remove('sync-pending');
                syncQueueIndicator.onclick = null;
            }
        } catch (err) {
            console.error('Failed to check pending syncs:', err);
        }
    }

    // ── Register Service Worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        // Wait for page load to register SW
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    console.log('SW registered:', reg.scope);

                    // Check for updates
                    reg.addEventListener('updatefound', () => {
                        console.log('SW update found');
                        const newWorker = reg.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('SW update available, refresh to update');
                                showBanner('New version available. Refresh to update.', 'info');
                            }
                        });
                    });
                })
                .catch(err => console.warn('SW registration failed:', err));
        });

        // Handle SW messages
        navigator.serviceWorker.addEventListener('message', ({ data }) => {
            console.log('Message from SW:', data);

            if (data?.type === 'SYNC_COMPLETE') {
                showBanner('Patient record synced successfully!', 'success');
                checkPendingSyncs();

                if (data.patient_id && !window.location.pathname.includes(`/patient/${data.patient_id}`)) {
                    setTimeout(() => {
                        window.location.href = `/patient/${data.patient_id}`;
                    }, 1500);
                }
            }
            if (data?.type === 'SYNC_FAILED') {
                showBanner('Sync failed — will retry automatically', 'warning');
                checkPendingSyncs();
            }
            if (data?.type === 'SYNC_VALIDATION_ERROR') {
                showBanner('Record was rejected by server — please check data', 'error');
                checkPendingSyncs();
            }
            if (data?.type === 'SYNC_SUMMARY') {
                console.log('Sync summary:', data);
            }
            if (data?.type === 'SW_READY') {
                console.log('Service Worker is ready');
                // Trigger sync if there are pending records
                checkPendingSyncs();
            }
        });

        // Handle controller change (SW activation)
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('Service Worker controller changed');
            checkPendingSyncs();
        });
    } else {
        console.warn('Service Worker not supported, using direct sync only');
        // Setup polling sync as fallback
        setupPollingSync();
    }

    // ── Online / Offline Event Handlers ──────────────────────────────────────
    const updateOnlineUI = () => {
        updateUIMode();

        if (navigator.onLine) {
            showBanner('Back online! Syncing pending records...', 'success');
            triggerBackgroundSync();
        } else {
            showBanner('You are offline. Data will be saved locally and synced when online.', 'warning');
        }
    };

    window.addEventListener('online', updateOnlineUI);
    window.addEventListener('offline', updateOnlineUI);

    // Also sync when page becomes visible again
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && navigator.onLine) {
            console.log('Page visible, checking for pending syncs...');
            checkPendingSyncs();
        }
    });

    // Initial UI setup
    updateUIMode();

    // Initialize sync handling
    setTimeout(async () => {
        await checkPendingSyncs();
        setupPollingSync(); // This will only activate if Background Sync is not available
    }, 1000);

    // ── Main Form Submit Handler (Online) ─────────────────────────────────────
    const handleFormSubmit = async (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

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
                    retry_count: 0,
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
                retry_count: 0,
                created_at: Date.now()
            });

            showBanner('Saved offline! Will sync when online.', 'success');

            await checkPendingSyncs();

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
                sync_status: 'draft',
                server_id: null,
                form_data: formData,
                is_draft: true,
                retry_count: 0,
                created_at: Date.now()
            });

            showBanner('Draft saved locally', 'success');
            await checkPendingSyncs();

        } catch (dbErr) {
            console.error('Draft save failed:', dbErr);
            showBanner('Failed to save draft', 'error');
        }
    };

    // ── Build API Payload ────────────────────────────────────────────────────
    function buildApiPayload(formData) {
        const payload = {
            Patient: {},
            Tumour: {},
            Treatment: [],
            Sources: [],
            FollowUp: {},
            concurrent_illness: '',
            _geo: null
        };

        // Extract Patient fields (if they exist as an object)
        if (formData.Patient && typeof formData.Patient === 'object') {
            payload.Patient = { ...formData.Patient };
        }

        // Extract Tumour fields
        if (formData.Tumour && typeof formData.Tumour === 'object') {
            payload.Tumour = { ...formData.Tumour };
        }

        // Extract Treatment array
        if (formData.Treatment && Array.isArray(formData.Treatment)) {
            payload.Treatment = formData.Treatment.filter(t => t && Object.keys(t).length > 0);
        }

        // Extract Sources array
        if (formData.Sources && Array.isArray(formData.Sources)) {
            payload.Sources = formData.Sources.filter(s => s && Object.keys(s).length > 0);
        }

        // Extract FollowUp fields
        if (formData.FollowUp && typeof formData.FollowUp === 'object') {
            payload.FollowUp = { ...formData.FollowUp };
        }

        // Extract concurrent_illness (might be in formData directly or in Treatment object)
        if (formData.concurrent_illness) {
            payload.concurrent_illness = formData.concurrent_illness;
        } else if (formData.Treatment && formData.Treatment.concurrent_illness) {
            payload.concurrent_illness = formData.Treatment.concurrent_illness;
        }

        // Extract geo data
        if (formData._geo) {
            payload._geo = formData._geo;
        }

        // Clean up empty entries
        if (Object.keys(payload.Patient).length === 0) delete payload.Patient;
        if (Object.keys(payload.Tumour).length === 0) delete payload.Tumour;
        if (payload.Treatment.length === 0) delete payload.Treatment;
        if (payload.Sources.length === 0) delete payload.Sources;
        if (Object.keys(payload.FollowUp).length === 0) delete payload.FollowUp;
        if (!payload.concurrent_illness) delete payload.concurrent_illness;
        if (!payload._geo) delete payload._geo;

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

            await checkPendingSyncs();

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
            // Skip disabled fields and template placeholders
            if (key.includes('__index__')) continue;

            // Handle nested array fields: Model[index][field]
            const arrayMatch = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
            if (arrayMatch) {
                const [, model, index, field] = arrayMatch;
                const arrayKey = `${model}Array`;
                if (!data[arrayKey]) data[arrayKey] = [];
                if (!data[arrayKey][index]) data[arrayKey][index] = {};
                data[arrayKey][index][field] = value;
            }
            // Handle simple nested fields: Model[field]
            else {
                const nestedMatch = key.match(/^(\w+)\[(\w+)\]$/);
                if (nestedMatch) {
                    const [, model, field] = nestedMatch;
                    if (!data[model]) data[model] = {};
                    data[model][field] = value;
                } else {
                    // Flat fields
                    data[key] = value;
                }
            }
        }

        // Convert TreatmentArray to Treatment array
        if (data.TreatmentArray) {
            data.Treatment = data.TreatmentArray.filter(t => t && Object.keys(t).length > 0);
            delete data.TreatmentArray;
        }

        // Convert SourcesArray to Sources array
        if (data.SourcesArray) {
            data.Sources = data.SourcesArray.filter(s => s && Object.keys(s).length > 0);
            delete data.SourcesArray;
        }

        // Add geo data
        if (data.geo_lat && data.geo_lng) {
            data._geo = {
                lat: parseFloat(data.geo_lat),
                lng: parseFloat(data.geo_lng),
                accuracy: data.geo_accuracy ? parseFloat(data.geo_accuracy) : 0,
                captured_at: data.geo_captured || new Date().toISOString().replace('T', ' ').replace(/\.\d+/, '')
            };
            delete data.geo_lat;
            delete data.geo_lng;
            delete data.geo_accuracy;
            delete data.geo_captured;
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

    // ── Export debug functions (if debug mode is enabled) ─────────────────────
    window.syncDebug = {
        triggerSync: triggerBackgroundSync,
        directSync: directSyncFallback,
        checkPending: checkPendingSyncs,
        getStatus: async () => ({
            isOnline: navigator.onLine,
            pendingCount: pendingSyncCount,
            syncSupported: await isBackgroundSyncSupported(),
            pollingActive: pollingInterval !== null
        })
    };

    // ── Attach Event Listeners ────────────────────────────────────────────────
    /* form.addEventListener('submit', (e) => {
         e.preventDefault();
         if (navigator.onLine) {
             handleFormSubmit(e);
         } else {
             handleOfflineSave(e);
         }
     });*/

    // Only attach custom handlers to offline/draft buttons – normal submit goes to server
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

    console.log('Patient form initialized - Dynamic UI mode active with sync fallbacks');
});