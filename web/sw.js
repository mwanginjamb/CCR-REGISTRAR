/**
 * sw.js — Service Worker for Clinical Cancer Registry
 * Location: Web root directory (/sw.js)
 */

// Define constants
const CACHE_NAME = 'clinical-registry-v1.0.4';
const SYNC_TAG = 'sync-patient-records';
const API_SYNC_URL = '/patient-api/create';

// Log that SW is starting
console.log('[SW] Service Worker script loaded at:', new Date().toISOString());

// Assets to cache (adjust paths to match your actual files)
const SHELL_ASSETS = [
    '/',
    '/patient/create',
    '/css/tailwind.css',
    '/js/offline-db.js',
    '/js/geo-tag.js',
    '/js/patientForm.js',
    '/js/essentialTnmFields.js',
    '/manifest.json'
];

// Install event
self.addEventListener('install', (event) => {
    console.log('[SW] Install event started');

    event.waitUntil(
        (async () => {
            try {
                const cache = await caches.open(CACHE_NAME);
                console.log('[SW] Caching shell assets...');

                // Cache each asset individually to avoid one failing all
                for (const asset of SHELL_ASSETS) {
                    try {
                        await cache.add(asset);
                        console.log(`[SW] Cached: ${asset}`);
                    } catch (err) {
                        console.warn(`[SW] Failed to cache ${asset}:`, err);
                    }
                }

                console.log('[SW] Installation complete');
                await self.skipWaiting();

            } catch (err) {
                console.error('[SW] Installation failed:', err);
            }
        })()
    );
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('[SW] Activate event started');

    event.waitUntil(
        (async () => {
            // Clean up old caches
            const keys = await caches.keys();
            const oldCaches = keys.filter(key => key !== CACHE_NAME);

            await Promise.all(
                oldCaches.map(key => {
                    console.log(`[SW] Deleting old cache: ${key}`);
                    return caches.delete(key);
                })
            );

            console.log('[SW] Activation complete, claiming clients...');
            await self.clients.claim();

            // Notify all clients that SW is ready
            const clients = await self.clients.matchAll();
            clients.forEach(client => {
                client.postMessage({
                    type: 'SW_READY',
                    timestamp: Date.now()
                });
            });
        })()
    );
});

// Message handling
self.addEventListener('message', (event) => {
    console.log('[SW] Received message:', event.data);

    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data.type === 'TEST') {
        if (event.ports && event.ports[0]) {
            event.ports[0].postMessage({
                status: 'ok',
                timestamp: Date.now(),
                message: 'Service Worker is responding'
            });
        }
    }

    if (event.data.type === 'GET_PENDING_COUNT') {
        (async () => {
            const pending = await idbGetPending();
            if (event.ports && event.ports[0]) {
                event.ports[0].postMessage({ count: pending.length });
            }
        })();
    }
});

// Background sync event
self.addEventListener('sync', (event) => {
    console.log('[SW] Sync event received:', event.tag);

    if (event.tag === SYNC_TAG) {
        event.waitUntil(syncPendingRecords());
    }
});

// Fetch event for offline support
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // API calls - network first
    if (url.pathname.startsWith('/patient-api/')) {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Cache successful API responses if needed
                    return response;
                })
                .catch(() => {
                    return new Response(JSON.stringify({
                        error: 'Offline',
                        message: 'You are offline. Data will sync when connection returns.'
                    }), {
                        status: 503,
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
        return;
    }

    // Navigation requests - network first with cache fallback
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(event.request)
                        .then(response => {
                            return response || caches.match('/');
                        });
                })
        );
        return;
    }

    // Static assets - cache first
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return new Response('Asset not available offline', { status: 404 });
            })
    );
});

// Sync pending records function
async function syncPendingRecords() {
    console.log('[SW] Starting background sync...', new Date().toISOString());

    try {
        const pending = await idbGetPending();
        console.log(`[SW] Found ${pending.length} pending records to sync`);

        if (pending.length === 0) {
            console.log('[SW] No pending records');
            notifyClients({ type: 'SYNC_NO_RECORDS' });
            return;
        }

        let syncedCount = 0;
        let errorCount = 0;

        for (const record of pending) {
            console.log(`[SW] Syncing record ${record.local_id}...`);

            try {
                // Get CSRF token from stored data
                const csrfToken = record.form_data?._csrf || '';

                const response = await fetch(API_SYNC_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify(record.form_data)
                });

                console.log(`[SW] Record ${record.local_id} response:`, response.status);

                if (response.status === 422) {
                    const errorData = await response.json();
                    console.error(`[SW] Validation error for ${record.local_id}:`, errorData);
                    await idbMarkError(record.local_id, JSON.stringify(errorData.errors || errorData));
                    notifyClients({
                        type: 'SYNC_VALIDATION_ERROR',
                        local_id: record.local_id,
                        errors: errorData
                    });
                    errorCount++;
                    continue;
                }

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                console.log(`[SW] Record ${record.local_id} synced successfully, server_id: ${result.id}`);

                await idbMarkSynced(record.local_id, result.id);
                syncedCount++;

                notifyClients({
                    type: 'SYNC_COMPLETE',
                    local_id: record.local_id,
                    server_id: result.id
                });

            } catch (err) {
                console.error(`[SW] Failed to sync record ${record.local_id}:`, err);
                errorCount++;
                notifyClients({
                    type: 'SYNC_FAILED',
                    local_id: record.local_id,
                    error: err.message
                });
                // Don't mark as error - leave as pending for retry
            }
        }

        console.log(`[SW] Sync complete: ${syncedCount} synced, ${errorCount} errors`);
        notifyClients({
            type: 'SYNC_SUMMARY',
            synced: syncedCount,
            errors: errorCount,
            total: pending.length,
            timestamp: Date.now()
        });

    } catch (err) {
        console.error('[SW] Critical sync error:', err);
        notifyClients({
            type: 'SYNC_CRITICAL_ERROR',
            error: err.message
        });
    }
}

// IndexedDB helpers
const DB_NAME = 'ClinicalRegistryDB';
const STORE_NAME = 'patient_drafts';
const DB_VERSION = 2;

function idbOpen() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => {
            console.error('[SW] IndexedDB open error:', request.error);
            reject(request.error);
        };

        request.onsuccess = () => {
            console.log('[SW] IndexedDB opened successfully');
            resolve(request.result);
        };

        request.onupgradeneeded = (event) => {
            console.log('[SW] IndexedDB upgrade needed');
            const db = event.target.result;

            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, {
                    keyPath: 'local_id',
                    autoIncrement: true
                });
                store.createIndex('sync_status', 'sync_status', { unique: false });
                store.createIndex('server_id', 'server_id', { unique: false });
                store.createIndex('created_at', 'created_at', { unique: false });
                console.log('[SW] Created patient_drafts store');
            }

            if (!db.objectStoreNames.contains('sync_logs')) {
                const logStore = db.createObjectStore('sync_logs', {
                    keyPath: 'id',
                    autoIncrement: true
                });
                logStore.createIndex('timestamp', 'timestamp', { unique: false });
                console.log('[SW] Created sync_logs store');
            }
        };
    });
}

async function idbGetPending() {
    try {
        const db = await idbOpen();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction([STORE_NAME], 'readonly');
            const store = transaction.objectStore(STORE_NAME);
            const index = store.index('sync_status');
            const request = index.getAll('pending');

            request.onsuccess = () => {
                resolve(request.result || []);
            };

            request.onerror = () => {
                console.error('[SW] Get pending error:', request.error);
                reject(request.error);
            };

            transaction.oncomplete = () => {
                db.close();
            };
        });
    } catch (err) {
        console.error('[SW] idbGetPending failed:', err);
        return [];
    }
}

async function idbMarkSynced(localId, serverId) {
    const db = await idbOpen();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.get(localId);

        request.onsuccess = () => {
            const record = request.result;
            if (record) {
                record.sync_status = 'synced';
                record.server_id = serverId;
                record.synced_at = Date.now();
                store.put(record);
                console.log(`[SW] Record ${localId} marked as synced`);
            }
            resolve();
        };

        request.onerror = () => {
            console.error(`[SW] Failed to mark synced for ${localId}:`, request.error);
            reject(request.error);
        };

        transaction.oncomplete = () => {
            db.close();
        };
    });
}

async function idbMarkError(localId, errorMsg) {
    const db = await idbOpen();

    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        const request = store.get(localId);

        request.onsuccess = () => {
            const record = request.result;
            if (record) {
                record.sync_status = 'error';
                record.error_msg = errorMsg;
                record.error_at = Date.now();
                store.put(record);
                console.log(`[SW] Record ${localId} marked as error`);
            }
            resolve();
        };

        request.onerror = () => {
            console.error(`[SW] Failed to mark error for ${localId}:`, request.error);
            reject(request.error);
        };

        transaction.oncomplete = () => {
            db.close();
        };
    });
}

function notifyClients(message) {
    self.clients.matchAll().then(clients => {
        console.log(`[SW] Notifying ${clients.length} clients:`, message.type);
        clients.forEach(client => {
            client.postMessage(message);
        });
    });
}

console.log('[SW] Service Worker initialized successfully');