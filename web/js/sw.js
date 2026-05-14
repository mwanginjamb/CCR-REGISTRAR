/**
 * sw.js — Service Worker (Complete)
 */

const CACHE_NAME = 'clinical-registry-v1.0.1';
const SYNC_TAG = 'sync-patient-records';
const API_SYNC_URL = '/patient-api/create';

const SHELL_ASSETS = [
    '/',
    '/patient/create',
    '/css/tailwind.css',
    '/js/offline-db.js',
    '/js/geo-tag.js',
    '/js/patientForm.js',
    '/manifest.json'
];

// Install: pre-cache shell
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(SHELL_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate: clean stale caches
self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys
                .filter(k => k !== CACHE_NAME)
                .map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch: network-first for API, cache-first for assets
self.addEventListener('fetch', (e) => {
    const { request } = e;
    const url = new URL(request.url);

    // Network-first for API calls
    if (url.pathname.includes('/patient-api/') || url.pathname.includes('/api/')) {
        e.respondWith(networkFirst(request));
        return;
    }

    // Navigation: network-first with offline fallback
    if (request.mode === 'navigate') {
        e.respondWith(networkFirst(request, true));
        return;
    }

    // Static assets: cache-first
    e.respondWith(cacheFirst(request));
});

// Background Sync
self.addEventListener('sync', (e) => {
    if (e.tag === SYNC_TAG) {
        e.waitUntil(syncPendingRecords());
    }
});

async function syncPendingRecords() {
    console.log('Background sync started');
    const pending = await idbGetPending();

    for (const record of pending) {
        try {
            const response = await fetch(API_SYNC_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': record.form_data?._csrf || ''
                },
                body: JSON.stringify(record.form_data)
            });

            if (response.status === 422) {
                const json = await response.json();
                await idbMarkError(record.local_id, JSON.stringify(json.errors || json));
                notifyClients({
                    type: 'SYNC_VALIDATION_ERROR',
                    local_id: record.local_id,
                    errors: json
                });
                continue;
            }

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const json = await response.json();
            await idbMarkSynced(record.local_id, json.id);
            notifyClients({ type: 'SYNC_COMPLETE', server_id: json.id, patient_id: json.id });

        } catch (err) {
            console.error('Sync failed for record:', record.local_id, err);
            notifyClients({ type: 'SYNC_FAILED', local_id: record.local_id });
            // Leave as 'pending' for retry
        }
    }
}

// ADD MISSING FUNCTION: idbMarkError
async function idbMarkError(local_id, reason) {
    const db = await idbOpen();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(['patient_drafts'], 'readwrite');
        const store = tx.objectStore('patient_drafts');
        const get = store.get(local_id);
        get.onsuccess = () => {
            const rec = get.result;
            if (!rec) return resolve();
            rec.sync_status = 'error';
            rec.error_msg = reason;
            store.put(rec).onsuccess = resolve;
        };
        get.onerror = () => reject(get.error);
    });
}

// IndexedDB helpers
const DB_NAME = 'ClinicalRegistryDB';
const STORE_NAME = 'patient_drafts';

function idbOpen() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onsuccess = ({ target: { result } }) => resolve(result);
        req.onerror = () => reject(req.error);
        req.onupgradeneeded = ({ target: { result: db } }) => {
            if (!db.objectStoreNames.contains(STORE_NAME)) {
                const store = db.createObjectStore(STORE_NAME, { keyPath: 'local_id', autoIncrement: true });
                store.createIndex('sync_status', 'sync_status', { unique: false });
                store.createIndex('server_id', 'server_id', { unique: false });
            }
        };
    });
}

async function idbGetPending() {
    const db = await idbOpen();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readonly');
        const idx = tx.objectStore(STORE_NAME).index('sync_status');
        const req = idx.getAll('pending');
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => reject(req.error);
    });
}

async function idbMarkSynced(local_id, server_id) {
    const db = await idbOpen();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        const get = store.get(local_id);
        get.onsuccess = () => {
            const rec = get.result;
            if (!rec) return resolve();
            rec.sync_status = 'synced';
            rec.server_id = server_id;
            rec.synced_at = Date.now();
            store.put(rec).onsuccess = resolve;
        };
        get.onerror = () => reject(get.error);
    });
}

function notifyClients(msg) {
    self.clients.matchAll().then(clients =>
        clients.forEach(c => c.postMessage(msg))
    );
}

// Fetch strategies
async function networkFirst(request, useOfflineFallback = false) {
    try {
        const res = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, res.clone());
        return res;
    } catch (err) {
        const cached = await caches.match(request);
        if (cached) return cached;
        if (useOfflineFallback) {
            return caches.match('/offline.html') || new Response('You are offline', { status: 503 });
        }
        return new Response('Network error', { status: 503 });
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const res = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, res.clone());
        return res;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}