/**
 * form.js — intercepts patient form submit
 * Captures geolocation → serialises form → saves to IndexedDB →
 * attempts immediate API sync if online → registers background sync if offline.
 */
document.addEventListener('DOMContentLoaded', () => {

    const API_ENDPOINT = '/api/patient/create';   // Yii2 REST endpoint
    const SYNC_TAG = 'sync-patient-records';
    const form = document.getElementById('patient-form');
    const statusBanner = document.getElementById('sync-status-banner'); // add this in view

    if (!form) return;

    // ── Register Service Worker ──────────────────────────────────────────────
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            console.log('SW registered', reg.scope);
        });

        // Listen for sync-complete messages from SW
        navigator.serviceWorker.addEventListener('message', ({ data }) => {
            if (data?.type === 'SYNC_COMPLETE') showBanner('Records synced ✓', 'success');
            if (data?.type === 'SYNC_FAILED') showBanner('Sync failed — will retry', 'error');
        });
    }

    // -- SHOW SYNC ERRORS When they occur --

    navigator.serviceWorker.addEventListener('message', ({ data }) => {
        if (data?.type === 'SYNC_COMPLETE')
            showBanner('Records synced ✓', 'success');

        if (data?.type === 'SYNC_FAILED')
            showBanner('Sync failed — will retry automatically', 'warning');

        if (data?.type === 'SYNC_VALIDATION_ERROR')
            showBanner('A saved record was rejected by the server — please review', 'error');
    });

    // ── Online / Offline indicator ────────────────────────────────────────────
    const updateOnlineUI = () => {
        const pill = document.getElementById('connection-status');
        if (!pill) return;
        pill.textContent = navigator.onLine ? 'Online' : 'Offline';
        pill.className = navigator.onLine
            ? 'px-3 py-1 rounded-full text-xs font-bold bg-tertiary-container text-on-tertiary-container'
            : 'px-3 py-1 rounded-full text-xs font-bold bg-error-container text-on-error-container';
    };
    window.addEventListener('online', updateOnlineUI);
    window.addEventListener('offline', updateOnlineUI);
    updateOnlineUI();

    // ── Form Submit ───────────────────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving…';

        // 1. Capture geolocation (non-blocking, max 8 s)
        const coords = await GeoTag.capture();
        GeoTag.injectIntoForm(form);

        // 2. Serialise all form fields into a plain object
        const payload = serialiseForm(form);
        if (coords) payload._geo = coords;
        payload._csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        // 3. Save to IndexedDB with status 'pending'
        let local_id;
        try {
            local_id = await PatientDB.save({
                sync_status: 'pending',
                server_id: null,
                form_data: payload
            });
            showBanner('Saved locally', 'info');
        } catch (dbErr) {
            console.error('IndexedDB save failed', dbErr);
            showBanner('Local save failed!', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Finalize Abstract';
            return;
        }

        // 4a. If online → attempt immediate sync
        if (navigator.onLine) {
            await attemptSync(local_id, payload);
        } else {
            // 4b. Register background sync for when connection returns
            showBanner('Offline — will sync automatically when online', 'warning');
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register(SYNC_TAG);
            }
        }

        submitBtn.disabled = false;
        submitBtn.textContent = 'Finalize Abstract';
    });

    // ── Helpers ───────────────────────────────────────────────────────────────

    async function attemptSync(local_id, payload) {
        try {
            const res = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': payload._csrf ?? ''
                },
                body: JSON.stringify(payload)
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const json = await res.json();
            await PatientDB.markSynced(local_id, json.id);
            showBanner('Saved & synced ✓', 'success');

            // Optionally redirect to readonly view
            if (json.id) setTimeout(() => { window.location = `/patient/${json.id}`; }, 1200);

        } catch (err) {
            console.warn('Immediate sync failed, queuing for background sync', err);
            showBanner('Saved offline — syncing in background', 'warning');

            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('sync-patient-records');
            }
        }
    }

    /** Convert all named form inputs to a nested object */
    function serialiseForm(formEl) {
        const data = {};

        new FormData(formEl).forEach((value, key) => {
            // Yii2: Treatment[0][treatment_type] → parts: ['Treatment','0','treatment_type']
            const parts = key.replace(/\]/g, '').split('[');
            let node = data;

            parts.forEach((part, i) => {
                const isLast = i === parts.length - 1;
                const nextIsNumeric = !isLast && /^\d+$/.test(parts[i + 1]);

                if (isLast) {
                    node[part] = value;
                } else {
                    if (node[part] === undefined) {
                        // If next key is numeric, this level is an array
                        node[part] = nextIsNumeric ? [] : {};
                    }
                    node = node[part];
                }
            });
        });

        return data;
    }

    function showBanner(msg, type) {
        if (!statusBanner) return;
        const colours = {
            success: 'bg-tertiary-container text-on-tertiary-container',
            error: 'bg-error-container text-on-error-container',
            warning: 'bg-secondary-container text-on-secondary-container',
            info: 'bg-surface-container text-on-surface-variant'
        };
        statusBanner.textContent = msg;
        statusBanner.className = `fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl font-semibold shadow-lg transition-all ${colours[type] ?? colours.info}`;
        statusBanner.style.display = 'block';
        clearTimeout(statusBanner._timer);
        statusBanner._timer = setTimeout(() => { statusBanner.style.display = 'none'; }, 5000);
    }
});