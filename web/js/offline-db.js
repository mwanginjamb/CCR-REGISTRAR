/**
 * PatientDB — IndexedDB module (vanilla, no libs)
 * Schema: patient_drafts store
 */
const PatientDB = (() => {
    const DB_NAME = 'ClinicalRegistryDB-v1';
    const DB_VERSION = 1;
    const STORE = 'patient_drafts-v1';
    let _db = null;

    /** Open (or reuse) the DB connection */
    const open = () => new Promise((resolve, reject) => {
        if (_db) return resolve(_db);

        const req = indexedDB.open(DB_NAME, DB_VERSION);

        req.onupgradeneeded = ({ target: { result: db } }) => {
            if (!db.objectStoreNames.contains(STORE)) {
                const store = db.createObjectStore(STORE, {
                    keyPath: 'local_id',
                    autoIncrement: true
                });
                store.createIndex('sync_status', 'sync_status', { unique: false });
                store.createIndex('server_id', 'server_id', { unique: false });
            }
        };

        req.onsuccess = ({ target: { result: db } }) => { _db = db; resolve(db); };
        req.onerror = () => reject(req.error);
    });

    /** Save or update a draft record */
    const save = async (record) => {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            const store = tx.objectStore(STORE);
            const req = store.put({ ...record, updated_at: Date.now() });
            req.onsuccess = () => resolve(req.result); // returns local_id
            req.onerror = () => reject(req.error);
        });
    };

    /** Fetch all records with sync_status = 'pending' */
    const getPending = async () => {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readonly');
            const index = tx.objectStore(STORE).index('sync_status');
            const req = index.getAll('pending');
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    };

    /** Mark a local record as synced and attach server_id */
    const markSynced = async (local_id, server_id) => {
        const db = await open();
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
    };

    /** Mark a record as errored with a reasons */

    const markError = async (local_id, reason, statusCode = null, responseBody = null) => {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            const store = tx.objectStore(STORE);
            const get = store.get(local_id);
            get.onsuccess = () => {
                const rec = get.result;
                if (!rec) return resolve();
                rec.sync_status = 'error';
                rec.error_msg = reason;
                rec.error_status = statusCode;
                rec.error_response = responseBody;
                rec.updated_at = Date.now();
                store.put(rec).onsuccess = () => resolve();
            };
            get.onerror = () => reject(get.error);
        });
    };

    const getErrors = async () => {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readonly');
            const index = tx.objectStore(STORE).index('sync_status');
            const req = index.getAll('error');
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    };

    const resetToPending = async (local_id) => {
        const db = await open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            const store = tx.objectStore(STORE);
            const get = store.get(local_id);
            get.onsuccess = () => {
                const rec = get.result;
                if (!rec) return resolve();
                rec.sync_status = 'pending';
                rec.error_msg = null;
                rec.retry_count = 0;
                store.put(rec).onsuccess = () => resolve();
            };
            get.onerror = () => reject(get.error);
        });
    };






    return { open, save, getPending, markSynced, markError, getErrors, resetToPending };
})();
