/**
 * sw.js — Service Worker
 * Responsibilities:
 *   1. Cache shell assets (cache-first)
 *   2. Network-first for navigation / API calls
 *   3. Background Sync: flush pending IndexedDB records on reconnect
 */

const CACHE_NAME = 'clinical-registry-v1.0.0';
const SYNC_TAG = 'sync-patient-records';
const API_SYNC_URL = '/api/patient/create';

const SHELL_ASSETS = [
    '/',
    '/patient/create',
    '/css/app.css',
    '/js/offline-db.js',
    '/js/geo-tag.js',
    '/js/patientForm.js',
    '/manifest.json'
];

// ── Install: pre-cache shell ──────────────────────────────────────────────────
self.addEventListener('install', (e) => {
    e.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(SHELL_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// ── Activate: clean stale caches ─────────────────────────────────────────────
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

// ── Fetch: network-first for navigation & API, cache-first for assets ────────
self.addEventListener('fetch', (e) => {
    const { request } = e;
    const url = new URL(request.url);

    // Always network-first for API calls
    if (url.pathname.startsWith('/api/')) {
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

// ── Background Sync ───────────────────────────────────────────────────────────
self.addEventListener('sync', (e) => {
    if (e.tag === SYNC_TAG) {
        e.waitUntil(syncPendingRecords());
    }
});

async function syncPendingRecords() {
    const pending = await idbGetPending();

    for (const record of pending) {
        try {
            const res = await fetch(API_SYNC_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(record.form_data)
            });

            if (res.status === 422) {
                // Server rejected the data — mark as error, don't retry endlessly
                const json = await res.json();
                await idbMarkError(record.local_id, JSON.stringify(json.errors));
                notifyClients({ type: 'SYNC_VALIDATION_ERROR', local_id: record.local_id, errors: json });
                continue;                   // move on to next record
            }

            if (!res.ok) throw new Error(`HTTP ${res.status}`);  // 5xx → retry

            const json = await res.json();
            await idbMarkSynced(record.local_id, json.id);
            notifyClients({ type: 'SYNC_COMPLETE', server_id: json.id });

        } catch (err) {
            // Network or 5xx — leave as 'pending' so Background Sync retries
            notifyClients({ type: 'SYNC_FAILED', local_id: record.local_id });
        }
    }
}

// ── Minimal IndexedDB helpers (duplicated in SW scope — no shared modules) ───

const DB_NAME = 'ClinicalRegistryDB';
const STORE = 'patient_drafts';

function idbOpen() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onsuccess = ({ target: { result } }) => resolve(result);
        req.onerror = () => reject(req.error);
        req.onupgradeneeded = ({ target: { result: db } }) => {
            if (!db.objectStoreNames.contains(STORE)) {
                const s = db.createObjectStore(STORE, { keyPath: 'local_id', autoIncrement: true });
                s.createIndex('sync_status', 'sync_status', { unique: false });
                s.createIndex('server_id', 'server_id', { unique: false });
            }
        };
    });
}

async function idbGetPending() {
    const db = await idbOpen();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readonly');
        const idx = tx.objectStore(STORE).index('sync_status');
        const req = idx.getAll('pending');
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function idbMarkSynced(local_id, server_id) {
    const db = await idbOpen();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        const store = tx.objectStore(STORE);
        const get = store.get(local_id);
        get.onsuccess = () => {
            const rec = get.result;
            if (!rec) return resolve();
            rec.sync_status = 'synced';
            rec.server_id = server_id;
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

// ── Fetch strategies ──────────────────────────────────────────────────────────

async function networkFirst(request, useOfflineFallback = false) {
    try {
        const res = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, res.clone());
        return res;
    } catch {
        const cached = await caches.match(request);
        return cached ?? (useOfflineFallback
            ? caches.match('/offline.html')  // optional offline page
            : new Response('Network error', { status: 503 }));
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