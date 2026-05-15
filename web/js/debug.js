/**
 * debug.js - Debug module for offline-first form
 * Include this file only in development or via URL parameter
 */

(function () {
    'use strict';

    // Configuration
    const DEBUG_CONFIG = {
        enabled: false,
        apiEndpoint: '/patient-api/test',
        syncTag: 'sync-patient-records',
        dbName: 'ClinicalRegistryDB',
        dbVersion: 2,
        stores: ['patient_drafts', 'sync_logs']
    };

    // DOM elements cache
    let debugPanel = null;
    let isInitialized = false;

    // ── Helper Functions ──────────────────────────────────────────────────────
    function addDebugLog(message, type = 'info') {
        const logsDiv = document.getElementById('debug-logs');
        if (!logsDiv) {
            console.log(`[Debug] ${message}`);
            return;
        }

        const colors = {
            info: '#d4d4d4',
            success: '#4ec9b0',
            error: '#f48771',
            warning: '#ce9178'
        };

        const logEntry = document.createElement('div');
        logEntry.style.cssText = `
            border-bottom: 1px solid #333;
            padding: 4px 0;
            color: ${colors[type] || colors.info};
            font-size: 11px;
            font-family: monospace;
        `;
        logEntry.innerHTML = `[${new Date().toLocaleTimeString()}] ${message}`;
        logsDiv.appendChild(logEntry);
        logsDiv.scrollTop = logsDiv.scrollHeight;

        console.log(`[Debug] ${message}`);
    }

    function idbOpen() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DEBUG_CONFIG.dbName, DEBUG_CONFIG.dbVersion);
            req.onsuccess = ({ target: { result } }) => resolve(result);
            req.onerror = () => reject(req.error);
        });
    }

    // ── Core Debug Functions ─────────────────────────────────────────────────
    async function testApiConnection() {
        addDebugLog('Testing API connectivity...');
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(DEBUG_CONFIG.apiEndpoint, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                }
            });

            if (response.ok) {
                const data = await response.json();
                addDebugLog(`✅ API is reachable. Response: ${JSON.stringify(data)}`, 'success');
                return true;
            } else {
                addDebugLog(`⚠️ API responded with status ${response.status}: ${response.statusText}`, 'warning');
                return false;
            }
        } catch (err) {
            addDebugLog(`❌ API unreachable: ${err.message}`, 'error');
            return false;
        }
    }

    async function viewPendingRecords() {
        addDebugLog('Fetching pending records...');
        try {
            if (typeof PatientDB === 'undefined') {
                addDebugLog('❌ PatientDB not available', 'error');
                return [];
            }

            const pending = await PatientDB.getPending();
            addDebugLog(`📋 Found ${pending.length} pending records:`, 'info');

            if (pending.length === 0) {
                addDebugLog('No pending records found', 'info');
                return [];
            }

            pending.forEach((record, i) => {
                addDebugLog(`  ${i + 1}. ID: ${record.local_id}`, 'info');
                addDebugLog(`     Created: ${new Date(record.created_at).toLocaleString()}`, 'info');
                addDebugLog(`     Status: ${record.sync_status}`, 'info');

                if (record.form_data) {
                    const patientName = record.form_data.Patient?.full_name || 'Unknown';
                    const hasGeo = !!record.form_data._geo;
                    addDebugLog(`     Patient: ${patientName}`, 'info');
                    addDebugLog(`     Has Geo: ${hasGeo}`, 'info');

                    if (record.validation_status) {
                        addDebugLog(`     Validation: ${record.validation_status}`,
                            record.validation_status === 'passed' ? 'success' : 'warning');
                    }
                }
            });

            // Console table for better viewing
            console.table(pending.map(p => ({
                local_id: p.local_id,
                created_at: new Date(p.created_at).toLocaleString(),
                patient_name: p.form_data?.Patient?.full_name || 'Unknown',
                has_geo: !!p.form_data?._geo,
                validation: p.validation_status || 'unknown'
            })));

            return pending;
        } catch (err) {
            addDebugLog(`❌ Failed to fetch pending records: ${err.message}`, 'error');
            return [];
        }
    }

    async function getSyncLogs() {
        try {
            const db = await idbOpen();
            if (!db.objectStoreNames.contains('sync_logs')) {
                addDebugLog('No sync_logs store found in IndexedDB', 'warning');
                return [];
            }

            return new Promise((resolve, reject) => {
                const tx = db.transaction(['sync_logs'], 'readonly');
                const store = tx.objectStore('sync_logs');
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = () => reject(req.error);
            });
        } catch (err) {
            console.error('Failed to get sync logs:', err);
            return [];
        }
    }

    async function exportSyncLogs() {
        addDebugLog('Exporting sync logs...');
        try {
            const logs = await getSyncLogs();
            const dataStr = JSON.stringify(logs, null, 2);
            const blob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sync-logs-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
            addDebugLog(`✅ Exported ${logs.length} sync logs`, 'success');
        } catch (err) {
            addDebugLog(`❌ Failed to export logs: ${err.message}`, 'error');
        }
    }

    async function forceSyncNow() {
        addDebugLog('Forcing manual sync...', 'info');

        if (!navigator.onLine) {
            addDebugLog('Cannot sync while offline', 'error');
            return false;
        }

        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register(DEBUG_CONFIG.syncTag);
                addDebugLog('✅ Sync triggered successfully', 'success');
                return true;
            } catch (err) {
                addDebugLog(`❌ Failed to trigger sync: ${err.message}`, 'error');
                return false;
            }
        } else {
            addDebugLog('Background sync not supported, attempting direct sync...', 'warning');
            return await directSync();
        }
    }

    async function directSync() {
        addDebugLog('Attempting direct sync...', 'warning');
        try {
            const pending = await PatientDB.getPending();
            let successCount = 0;

            for (const record of pending) {
                try {
                    const response = await fetch('/patient-api/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify(record.form_data)
                    });

                    if (response.ok) {
                        const result = await response.json();
                        await PatientDB.markSynced(record.local_id, result.id);
                        addDebugLog(`✅ Direct sync success for record ${record.local_id}`, 'success');
                        successCount++;
                    } else {
                        addDebugLog(`❌ Direct sync failed for record ${record.local_id}: ${response.status}`, 'error');
                    }
                } catch (err) {
                    addDebugLog(`❌ Direct sync error for record ${record.local_id}: ${err.message}`, 'error');
                }
            }

            addDebugLog(`Direct sync complete: ${successCount}/${pending.length} successful`,
                successCount === pending.length ? 'success' : 'warning');
            return successCount > 0;
        } catch (err) {
            addDebugLog(`❌ Direct sync failed: ${err.message}`, 'error');
            return false;
        }
    }

    async function clearAllPendingRecords() {
        if (!confirm('⚠️ WARNING: This will delete ALL pending records. Are you sure?')) {
            addDebugLog('Clear operation cancelled', 'warning');
            return;
        }

        addDebugLog('Clearing all pending records...', 'warning');
        try {
            const pending = await PatientDB.getPending();
            let cleared = 0;

            for (const record of pending) {
                await PatientDB.markSynced(record.local_id, 'cleared_by_user');
                cleared++;
            }

            addDebugLog(`✅ Cleared ${cleared} pending records`, 'success');
            await viewPendingRecords(); // Refresh display
        } catch (err) {
            addDebugLog(`❌ Failed to clear records: ${err.message}`, 'error');
        }
    }

    async function retryFailedRecords() {
        addDebugLog('Retrying failed records...');
        try {
            const db = await idbOpen();
            const tx = db.transaction(['patient_drafts'], 'readwrite');
            const store = tx.objectStore('patient_drafts');

            const allRecords = await new Promise((resolve) => {
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result);
            });

            const errorRecords = allRecords.filter(r => r.sync_status === 'error');
            addDebugLog(`Found ${errorRecords.length} failed records to retry`, 'info');

            for (const record of errorRecords) {
                record.sync_status = 'pending';
                record.retry_count = (record.retry_count || 0) + 1;
                record.retry_at = Date.now();

                await new Promise((resolve) => {
                    store.put(record).onsuccess = resolve;
                });

                addDebugLog(`Reset record ${record.local_id} for retry (attempt ${record.retry_count})`, 'info');
            }

            addDebugLog(`✅ Reset ${errorRecords.length} records for retry`, 'success');

            if (navigator.onLine && errorRecords.length > 0) {
                await forceSyncNow();
            }
        } catch (err) {
            addDebugLog(`❌ Failed to retry records: ${err.message}`, 'error');
        }
    }

    async function inspectRecord(localId) {
        addDebugLog(`Inspecting record ${localId}...`);
        try {
            const db = await idbOpen();
            const record = await new Promise((resolve) => {
                const tx = db.transaction(['patient_drafts'], 'readonly');
                const store = tx.objectStore('patient_drafts');
                const req = store.get(parseInt(localId));
                req.onsuccess = () => resolve(req.result);
            });

            if (record) {
                addDebugLog(`📄 Record ${localId} details:`, 'info');
                addDebugLog(`  - Created: ${new Date(record.created_at).toLocaleString()}`);
                addDebugLog(`  - Sync status: ${record.sync_status}`);
                addDebugLog(`  - Has form_data: ${!!record.form_data}`);

                if (record.form_data) {
                    addDebugLog(`  - Patient: ${record.form_data.Patient?.full_name || 'Unknown'}`);
                    addDebugLog(`  - Has geo: ${!!record.form_data._geo}`);
                    if (record.form_data._geo) {
                        addDebugLog(`  - Coordinates: ${record.form_data._geo.lat}, ${record.form_data._geo.lng}`);
                    }
                }

                if (record.validation_errors) {
                    addDebugLog(`  - Validation errors: ${JSON.stringify(record.validation_errors)}`, 'error');
                }

                if (record.error_msg) {
                    addDebugLog(`  - Error: ${record.error_msg}`, 'error');
                }

                console.log('Full record:', record);
            } else {
                addDebugLog(`❌ Record ${localId} not found`, 'error');
            }

            return record;
        } catch (err) {
            addDebugLog(`❌ Failed to inspect record: ${err.message}`, 'error');
            return null;
        }
    }

    async function diagnoseSyncIssues() {
        addDebugLog('========== 🔍 SYNC DIAGNOSTIC ==========');

        // 1. Online status
        addDebugLog(`📡 Online status: ${navigator.onLine ? '✅ Online' : '❌ Offline'}`,
            navigator.onLine ? 'success' : 'error');

        // 2. Service Worker
        let swRegistered = false;
        if ('serviceWorker' in navigator) {
            const registrations = await navigator.serviceWorker.getRegistrations();
            swRegistered = registrations.length > 0;
            addDebugLog(`📦 Service Worker: ${swRegistered ? '✅ Registered' : '❌ Not registered'}`);
            if (swRegistered && registrations[0].active) {
                addDebugLog(`   SW state: ${registrations[0].active.state}`);
            }
        } else {
            addDebugLog(`❌ Service Worker not supported`, 'error');
        }

        // 3. Background Sync
        const syncSupported = 'SyncManager' in window;
        addDebugLog(`🔄 Background Sync: ${syncSupported ? '✅ Supported' : '❌ Not supported'}`);

        // 4. IndexedDB
        try {
            const db = await idbOpen();
            addDebugLog(`💾 IndexedDB: ✅ Accessible`);
            addDebugLog(`   Stores: ${Array.from(db.objectStoreNames).join(', ')}`);
            db.close();
        } catch (err) {
            addDebugLog(`💾 IndexedDB: ❌ Failed: ${err.message}`, 'error');
        }

        // 5. Pending records
        if (typeof PatientDB !== 'undefined') {
            const pending = await PatientDB.getPending();
            addDebugLog(`📋 Pending records: ${pending.length}`);

            if (pending.length > 0) {
                pending.slice(0, 3).forEach((record, i) => {
                    addDebugLog(`   ${i + 1}. ID: ${record.local_id}, Created: ${new Date(record.created_at).toLocaleString()}`);
                });
            }

            const errors = await PatientDB.getValidationErrors();
            addDebugLog(`⚠️ Records with errors: ${errors.length}`);
        } else {
            addDebugLog(`❌ PatientDB not available`, 'error');
        }

        // 6. API connectivity
        const apiReachable = await testApiConnection();

        // 7. CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        addDebugLog(`🔐 CSRF token: ${csrfToken ? '✅ Present' : '❌ Missing'}`);

        addDebugLog('========== DIAGNOSTIC COMPLETE ==========');

        return {
            isOnline: navigator.onLine,
            hasSW: swRegistered,
            hasSyncSupport: syncSupported,
            apiReachable: apiReachable,
            hasCsrf: !!csrfToken
        };
    }

    // ── UI Panel Creation ────────────────────────────────────────────────────
    function createDebugPanel() {
        if (debugPanel) {
            debugPanel.style.display = 'block';
            return debugPanel;
        }

        const panel = document.createElement('div');
        panel.id = 'sync-debug-panel';
        panel.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 450px;
            max-height: 550px;
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 8px;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            z-index: 10000;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: block;
            border: 1px solid #444;
        `;

        panel.innerHTML = `
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 5px;">
                <strong>🔍 Sync Debug Console</strong>
                <button id="close-debug-panel" style="background: none; border: none; color: #fff; cursor: pointer; font-size: 16px;">✕</button>
            </div>
            <div id="debug-logs" style="max-height: 400px; overflow-y: auto; margin-bottom: 10px;">
                <div>Debug console ready. ${new Date().toLocaleTimeString()}</div>
            </div>
            <div style="margin-top: 10px; border-top: 1px solid #444; padding-top: 5px; display: flex; flex-wrap: wrap; gap: 5px;">
                <button id="clear-debug-logs" style="background: #444; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Clear</button>
                <button id="force-sync-now" style="background: #007acc; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Force Sync</button>
                <button id="view-pending-records" style="background: #6c757d; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">View Pending</button>
                <button id="retry-failed-records" style="background: #e67e22; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Retry Failed</button>
                <button id="export-sync-logs" style="background: #28a745; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Export Logs</button>
                <button id="clear-pending-records" style="background: #dc3545; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Clear All</button>
                <button id="run-diagnostic" style="background: #6610f2; border: none; color: #fff; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Diagnose</button>
            </div>
            <div style="margin-top: 8px; font-size: 10px; color: #888; border-top: 1px solid #444; padding-top: 5px;">
                <span>🔧 Debug Mode | </span>
                <span id="debug-status">Ready</span>
            </div>
        `;

        document.body.appendChild(panel);
        debugPanel = panel;

        // Attach event listeners
        document.getElementById('close-debug-panel').onclick = () => {
            debugPanel.style.display = 'none';
        };

        document.getElementById('clear-debug-logs').onclick = () => {
            const logsDiv = document.getElementById('debug-logs');
            if (logsDiv) logsDiv.innerHTML = '<div>Logs cleared...</div>';
        };

        document.getElementById('force-sync-now').onclick = forceSyncNow;
        document.getElementById('view-pending-records').onclick = viewPendingRecords;
        document.getElementById('retry-failed-records').onclick = retryFailedRecords;
        document.getElementById('export-sync-logs').onclick = exportSyncLogs;
        document.getElementById('clear-pending-records').onclick = clearAllPendingRecords;
        document.getElementById('run-diagnostic').onclick = diagnoseSyncIssues;

        // Create toggle button
        createToggleButton();

        return panel;
    }

    function createToggleButton() {
        if (document.getElementById('debug-toggle-btn')) return;

        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'debug-toggle-btn';
        toggleBtn.textContent = '🐛 Debug';
        toggleBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #007acc;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-family: monospace;
            font-size: 12px;
            cursor: pointer;
            z-index: 10001;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        `;

        toggleBtn.onclick = () => {
            if (debugPanel) {
                debugPanel.style.display = debugPanel.style.display === 'none' ? 'block' : 'none';
            } else {
                createDebugPanel();
            }
        };

        document.body.appendChild(toggleBtn);
    }

    // ── Service Worker Message Handling ──────────────────────────────────────
    function setupSyncMonitoring() {
        if (!navigator.serviceWorker) return;

        navigator.serviceWorker.addEventListener('message', ({ data }) => {
            addDebugLog(`📨 SW Message: ${data.type}`, 'info');

            switch (data.type) {
                case 'SYNC_COMPLETE':
                    addDebugLog(`✅ Record ${data.local_id} synced! Server ID: ${data.server_id}`, 'success');
                    break;
                case 'SYNC_FAILED':
                    addDebugLog(`❌ Record ${data.local_id} failed: ${data.error}`, 'error');
                    break;
                case 'SYNC_VALIDATION_ERROR':
                    addDebugLog(`⚠️ Validation error for record ${data.local_id}`, 'warning');
                    if (data.errors) {
                        addDebugLog(`   ${JSON.stringify(data.errors).substring(0, 200)}`, 'warning');
                    }
                    break;
                case 'SYNC_SUMMARY':
                    addDebugLog(`📊 Sync summary: ${data.synced} synced, ${data.errors} errors`,
                        data.errors > 0 ? 'warning' : 'success');
                    break;
                default:
                    addDebugLog(`Unknown message type: ${data.type}`, 'warning');
            }
        });
    }

    // ── Initialization ───────────────────────────────────────────────────────
    function init() {
        if (isInitialized) return;

        addDebugLog('Debug module initializing...');
        createDebugPanel();
        setupSyncMonitoring();

        // Make debug functions available globally
        window.patientDebug = {
            checkPending: viewPendingRecords,
            forceSync: forceSyncNow,
            clearAll: clearAllPendingRecords,
            exportLogs: exportSyncLogs,
            inspect: inspectRecord,
            testApi: testApiConnection,
            diagnose: diagnoseSyncIssues,
            retryFailed: retryFailedRecords,
            getLogs: getSyncLogs,
            directSync: directSync
        };

        addDebugLog('✅ Debug module initialized');
        addDebugLog('💡 Commands available: window.patientDebug.diagnose()');

        isInitialized = true;
    }

    // Auto-initialize if debug is enabled
    function checkAndInit() {
        const urlParams = new URLSearchParams(window.location.search);
        const debugEnabled = urlParams.has('debug') ||
            urlParams.has('dev') ||
            localStorage.getItem('enable_debug') === 'true' ||
            window.location.hostname === 'localhost' ||
            window.location.hostname === '127.0.0.1';

        if (debugEnabled) {
            DEBUG_CONFIG.enabled = true;
            init();
        } else {
            console.log('Debug mode disabled. Add ?debug=1 to URL to enable.');
        }
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkAndInit);
    } else {
        checkAndInit();
    }

})();