/**
 * patientForm.js - Hardened offline-first form handler
 * Uses multiple strategies to detect true offline status
 */

document.addEventListener('DOMContentLoaded', () => {
    const API_ENDPOINT = '/patient-api/create';
    const SYNC_TAG = 'sync-patient-records';
    const form = document.getElementById('patient-form');
    const statusBanner = document.getElementById('sync-status-banner');
    const submitBtn = document.getElementById('submit-form-default');
    const offlineSaveBtn = document.getElementById('offline-save-btn');
    const saveDraftBtn = document.getElementById('save-draft-btn');  // Fixed!
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
    let healthCheckInterval = null;
    let isTrulyOnline = true;
    let lastHealthCheckTime = 0;

    const MAX_SYNC_RETRIES = 5;
    const POLLING_INTERVAL = 30000; // 30 seconds
    const HEALTH_CHECK_INTERVAL = 15000; // 15 seconds
    const HEALTH_CHECK_TIMEOUT = 5000; // 5 seconds
    const API_HEALTH_ENDPOINT = '/patient-api/health';

    // ── True Online Detection (Multiple Strategies) ──────────────────────────

    /**
     * Strategy 1: Actual network request to health endpoint
     * Most reliable - tests if the API is actually reachable
     */
    async function checkReachability() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), HEALTH_CHECK_TIMEOUT);

            const response = await fetch(API_HEALTH_ENDPOINT, {
                method: 'HEAD',
                signal: controller.signal,
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });

            clearTimeout(timeoutId);
            return response.ok;
        } catch (err) {
            console.debug('Health check failed:', err.message);
            return false;
        }
    }

    /**
     * Strategy 2: DNS resolution check via image loading
     * Tests if DNS is working (works in airplane mode where navigator.onLine may still be true)
     */
    function checkDnsViaImage() {
        return new Promise((resolve) => {
            const img = new Image();
            const timeout = setTimeout(() => {
                img.src = '';
                resolve(false);
            }, 3000);

            img.onload = () => {
                clearTimeout(timeout);
                resolve(true);
            };

            img.onerror = () => {
                clearTimeout(timeout);
                resolve(false);
            };

            // Use a reliable, small image from your domain or CDN
            img.src = '/favicon.ico?t=' + Date.now();
        });
    }

    /**
     * Strategy 3: Navigator connection check (modern browsers)
     * Checks actual network connectivity state
     */
    function checkConnectionAPI() {
        if ('connection' in navigator) {
            const conn = navigator.connection;
            // If offline === true, definitely offline
            if (conn.offline === true) return false;
            // If rtt is 0 and downlink is 0, likely offline
            if (conn.rtt === 0 && conn.downlink === 0) return false;
            // If save-data is enabled, still might be online but cautious
            if (conn.saveData === true) {
                console.log('Save data mode enabled - assuming cautious online');
            }
        }
        return null; // inconclusive
    }

    /**
     * Strategy 4: Online status using multiple checks
     * Combines all strategies for most accurate result
     */
    let cachedOnlineStatus = true;
    let lastStatusCheck = 0;
    const STATUS_CACHE_DURATION = 5000; // Cache for 5 seconds

    async function getTrueOnlineStatus(forceCheck = false) {
        const now = Date.now();

        // Return cached status if within duration and not forced
        if (!forceCheck && (now - lastStatusCheck) < STATUS_CACHE_DURATION) {
            return cachedOnlineStatus;
        }

        let isOnline = false;

        // First try navigator.onLine (fast but unreliable)
        const navOnline = navigator.onLine;

        // Check Connection API
        const connStatus = checkConnectionAPI();
        if (connStatus === false) {
            isOnline = false;
        } else if (connStatus === true) {
            isOnline = true;
        } else {
            // Inconclusive, do actual reachability tests
            // Try multiple checks in parallel for speed
            const [reachable, dnsWorks] = await Promise.all([
                checkReachability(),
                checkDnsViaImage()
            ]);

            isOnline = reachable || dnsWorks;

            // If navOnline says true but reachability says false, we're likely in airplane mode
            if (navOnline === true && !reachable && !dnsWorks) {
                console.warn('navigator.onLine reported true but actual network is unavailable (airplane mode detected)');
                isOnline = false;
            }
        }

        // Update cache
        cachedOnlineStatus = isOnline;
        lastStatusCheck = now;

        console.log(`True online status: ${isOnline ? 'ONLINE' : 'OFFLINE'} (nav.onLine: ${navOnline})`);

        return isOnline;
    }

    /**
     * Continuous health monitoring - periodically checks actual connectivity
     */
    function startHealthMonitoring() {
        if (healthCheckInterval) {
            clearInterval(healthCheckInterval);
        }

        healthCheckInterval = setInterval(async () => {
            const wasOnline = isTrulyOnline;
            isTrulyOnline = await getTrueOnlineStatus(true);

            // If status changed, trigger UI update
            if (wasOnline !== isTrulyOnline) {
                console.log(`Connectivity changed: ${wasOnline ? 'ONLINE' : 'OFFLINE'} -> ${isTrulyOnline ? 'ONLINE' : 'OFFLINE'}`);
                updateUIMode();

                if (isTrulyOnline) {
                    showBanner('Connection restored! Syncing pending records...', 'success');
                    await triggerBackgroundSync();
                } else {
                    showBanner('Connection lost. Data will be saved locally.', 'warning');
                }
            }
        }, HEALTH_CHECK_INTERVAL);
    }

    // ── Enhanced Online/Offline Event Handlers ──────────────────────────────
    // Don't rely solely on navigator.onLine events - do actual checks
    window.addEventListener('online', async () => {
        console.log('Browser online event fired');
        // Verify with actual check
        const trulyOnline = await getTrueOnlineStatus(true);
        if (trulyOnline) {
            updateUIMode();
            showBanner('Back online! Syncing pending records...', 'success');
            await triggerBackgroundSync();
        } else {
            console.warn('Browser online event fired but network not actually available');
        }
    });

    window.addEventListener('offline', async () => {
        console.log('Browser offline event fired');
        isTrulyOnline = false;
        updateUIMode();
        showBanner('You are offline. Data will be saved locally and synced when online.', 'warning');
    });

    // Monitor page visibility - recheck when page becomes visible
    document.addEventListener('visibilitychange', async () => {
        if (!document.hidden) {
            console.log('Page became visible, checking connectivity...');
            const trulyOnline = await getTrueOnlineStatus(true);
            if (trulyOnline !== isTrulyOnline) {
                updateUIMode();
            }
            if (trulyOnline) {
                await checkPendingSyncs();
            }
        }
    });

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

        // Use true online status instead of navigator.onLine
        const trulyOnline = await getTrueOnlineStatus();
        if (!trulyOnline) return false;

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
                    console.log(`Retrying ${record.local_id} in ${delay}ms (attempt ${currentRetry + 1}/${maxRetries})...`);
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2;
                    currentRetry++;
                    continue;
                }

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

    window.retryFailedRecords = retryFailedRecords;

    // ── Trigger Background Sync with fallback ─────────────────────────────────
    async function triggerBackgroundSync() {
        const trulyOnline = await getTrueOnlineStatus();
        if (!trulyOnline) {
            console.log('Cannot sync while truly offline');
            return false;
        }

        const syncSupported = await isBackgroundSyncSupported();

        if (!syncSupported) {
            console.log('Background sync not available, using direct sync');
            return await directSyncFallback();
        }

        try {
            console.log('Waiting for Service Worker to be ready...');
            const registration = await navigator.serviceWorker.ready;

            if (!registration.active) {
                console.log('SW not active yet, waiting for activation...');

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

            console.log('Registering background sync...');
            await registration.sync.register(SYNC_TAG);
            console.log('Background sync registered successfully');
            showBanner('Background sync scheduled', 'info');
            return true;

        } catch (err) {
            console.error('Background sync registration failed:', err);

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
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }

        (async () => {
            const syncAvailable = await isBackgroundSyncSupported();
            if (syncAvailable) {
                console.log('Background Sync available, skipping polling fallback');
                return;
            }

            console.log('Setting up polling sync fallback (every 30 seconds)');

            const poll = async () => {
                const trulyOnline = await getTrueOnlineStatus();
                if (trulyOnline) {
                    console.log('Polling sync check...');
                    await directSyncFallback();
                }
            };

            pollingInterval = setInterval(poll, POLLING_INTERVAL);

            window.addEventListener('online', async () => {
                const trulyOnline = await getTrueOnlineStatus(true);
                if (trulyOnline) {
                    console.log('Online detected via event, verifying...');
                    await directSyncFallback();
                }
            });

            setTimeout(poll, 5000);
        })();
    }

    // ── Cleanup polling on page unload ────────────────────────────────────────
    window.addEventListener('beforeunload', () => {
        if (pollingInterval) clearInterval(pollingInterval);
        if (healthCheckInterval) clearInterval(healthCheckInterval);
    });

    // ── UI Mode Management (Uses true online status) ─────────────────────────
    async function updateUIMode() {
        const isOnline = await getTrueOnlineStatus();
        isTrulyOnline = isOnline;

        if (isOnline) {
            document.body.classList.remove('offline-mode');
            document.body.classList.add('online-mode');

            if (modeIndicator) {
                modeIndicator.textContent = 'LIVE MODE';
                modeIndicator.className = 'px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-green-100 text-green-800';
            }

            if (submitBtn) submitBtn.style.display = 'flex';
            if (offlineSaveBtn) offlineSaveBtn.style.display = 'none';

            const draftText = document.getElementById('save-draft-text');
            if (draftText) draftText.textContent = 'Save as Draft';

            await checkPendingSyncs();

        } else {
            document.body.classList.remove('online-mode');
            document.body.classList.add('offline-mode');

            if (modeIndicator) {
                modeIndicator.textContent = 'DRAFT MODE (OFFLINE)';
                modeIndicator.className = 'px-3 py-1 rounded-full text-[10px] md:text-xs font-bold bg-yellow-100 text-yellow-800';
            }

            if (submitBtn) submitBtn.style.display = 'none';
            if (offlineSaveBtn) offlineSaveBtn.style.display = 'flex';

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
                syncQueueIndicator.style.cursor = 'pointer';
                syncQueueIndicator.onclick = async () => {
                    showBanner('Manual sync triggered...', 'info');
                    await triggerBackgroundSync();
                };
                syncQueueIndicator.title = `${pendingSyncCount} pending sync(s) - Click to sync now`;

                const trulyOnline = await getTrueOnlineStatus();
                if (trulyOnline && pendingSyncCount > 0) {
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

    // Add health check endpoint to API controller (temporary)
    async function ensureHealthEndpoint() {
        // This will be added to PatientApiController
        console.log('Health endpoint should be added to PatientApiController: actionHealth()');
    }

    // ── Register Service Worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    console.log('SW registered:', reg.scope);
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
                checkPendingSyncs();
            }
        });

        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('Service Worker controller changed');
            checkPendingSyncs();
        });
    } else {
        console.warn('Service Worker not supported, using direct sync only');
        setupPollingSync();
    }

    // Also sync when page becomes visible again
    document.addEventListener('visibilitychange', async () => {
        if (!document.hidden) {
            const trulyOnline = await getTrueOnlineStatus();
            if (trulyOnline) {
                console.log('Page visible, checking for pending syncs...');
                await checkPendingSyncs();
            }
        }
    });

    // Start health monitoring
    startHealthMonitoring();

    // Initial UI setup
    (async () => {
        await updateUIMode();
        setTimeout(async () => {
            await checkPendingSyncs();
            setupPollingSync();
        }, 1000);
    })();

    // ── Main Form Submit Handler (Online) ─────────────────────────────────────
    const handleFormSubmit = async (event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const trulyOnline = await getTrueOnlineStatus();
        if (!trulyOnline) {
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

        if (formData.Patient && typeof formData.Patient === 'object') {
            payload.Patient = { ...formData.Patient };
        }

        if (formData.Tumour && typeof formData.Tumour === 'object') {
            payload.Tumour = { ...formData.Tumour };
        }

        if (formData.Treatment && Array.isArray(formData.Treatment)) {
            payload.Treatment = formData.Treatment.filter(t => t && Object.keys(t).length > 0);
        }

        if (formData.Sources && Array.isArray(formData.Sources)) {
            payload.Sources = formData.Sources.filter(s => s && Object.keys(s).length > 0);
        }

        if (formData.FollowUp && typeof formData.FollowUp === 'object') {
            payload.FollowUp = { ...formData.FollowUp };
        }

        if (formData.concurrent_illness) {
            payload.concurrent_illness = formData.concurrent_illness;
        } else if (formData.Treatment && formData.Treatment.concurrent_illness) {
            payload.concurrent_illness = formData.Treatment.concurrent_illness;
        }

        if (formData._geo) {
            payload._geo = formData._geo;
        }

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
            if (key.includes('__index__')) continue;

            const arrayMatch = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
            if (arrayMatch) {
                const [, model, index, field] = arrayMatch;
                const arrayKey = `${model}Array`;
                if (!data[arrayKey]) data[arrayKey] = [];
                if (!data[arrayKey][index]) data[arrayKey][index] = {};
                data[arrayKey][index][field] = value;
            } else {
                const nestedMatch = key.match(/^(\w+)\[(\w+)\]$/);
                if (nestedMatch) {
                    const [, model, field] = nestedMatch;
                    if (!data[model]) data[model] = {};
                    data[model][field] = value;
                } else {
                    data[key] = value;
                }
            }
        }

        if (data.TreatmentArray) {
            data.Treatment = data.TreatmentArray.filter(t => t && Object.keys(t).length > 0);
            delete data.TreatmentArray;
        }

        if (data.SourcesArray) {
            data.Sources = data.SourcesArray.filter(s => s && Object.keys(s).length > 0);
            delete data.SourcesArray;
        }

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
            success: 'bg-green-100 text-green-800 border-green-200',
            error: 'bg-red-100 text-red-800 border-red-200',
            warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            info: 'bg-blue-100 text-blue-800 border-blue-200'
        };

        statusBanner.textContent = msg;
        statusBanner.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl font-semibold shadow-lg border ${colours[type] || colours.info}`;
        statusBanner.style.display = 'block';

        if (statusBanner._timer) clearTimeout(statusBanner._timer);
        statusBanner._timer = setTimeout(() => {
            statusBanner.style.display = 'none';
        }, 5000);
    }

    // ── Export debug functions ────────────────────────────────────────────────
    window.syncDebug = {
        triggerSync: triggerBackgroundSync,
        directSync: directSyncFallback,
        checkPending: checkPendingSyncs,
        checkConnectivity: getTrueOnlineStatus,
        getStatus: async () => ({
            isOnline: await getTrueOnlineStatus(),
            navOnline: navigator.onLine,
            pendingCount: pendingSyncCount,
            syncSupported: await isBackgroundSyncSupported(),
            pollingActive: pollingInterval !== null,
            healthCheckActive: healthCheckInterval !== null
        })
    };

    // ── Attach Event Listeners ────────────────────────────────────────────────
    if (submitBtn) {
        submitBtn.addEventListener('click', handleFormSubmit);
    }

    if (offlineSaveBtn) {
        offlineSaveBtn.addEventListener('click', handleOfflineSave);
    }

    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', handleSaveDraft);
    }

    const backBtn = document.getElementById('back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.history.back();
        });
    }

    console.log('Patient form initialized - Hardened offline detection active');
});