// FinOps Billing - Service Worker for Offline Time Entry (PWA)
// Phase 17 - Story T.1
// Full offline capability with background sync

const CACHE_NAME = 'finops-field-v1';
const CACHE_URLS = [
    '/field/offline-time-entry',
    '/field/timesheet',
    '/css/app.css',
    '/js/app.js',
    '/offline',
    '/images/logo.png'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(CACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                if (response) {
                    return response;
                }

                const fetchRequest = event.request.clone();

                return fetch(fetchRequest).then((response) => {
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }

                    const responseToCache = response.clone();

                    if (event.request.url.startsWith(self.location.origin)) {
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });
                    }

                    return response;
                }).catch(() => {
                    return caches.match('/offline');
                });
            })
    );
});

// Background sync event
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync triggered:', event.tag);
    
    if (event.tag === 'sync-time-entries') {
        event.waitUntil(syncTimeEntries());
    }
});

// Sync time entries function
async function syncTimeEntries() {
    console.log('[Service Worker] Syncing time entries...');
    
    try {
        const db = await openDatabase();
        const unsyncedEntries = await getUnsyncedEntries(db);
        
        console.log(`[Service Worker] Found ${unsyncedEntries.length} unsynced entries`);
        
        for (const entry of unsyncedEntries) {
            try {
                // Get CSRF token from cookies
                const csrfToken = await getCsrfToken();
                
                const response = await fetch('/api/time-entries', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(entry)
                });
                
                if (response.ok) {
                    await markAsSynced(db, entry.id);
                    console.log(`[Service Worker] Synced entry ${entry.id}`);
                } else {
                    console.error(`[Service Worker] Failed to sync entry ${entry.id}`);
                }
            } catch (error) {
                console.error(`[Service Worker] Error syncing entry ${entry.id}:`, error);
            }
        }
        
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({
                type: 'SYNC_COMPLETE',
                count: unsyncedEntries.length
            });
        });
        
        console.log('[Service Worker] Sync complete');
    } catch (error) {
        console.error('[Service Worker] Sync failed:', error);
        throw error;
    }
}

// IndexedDB helpers
function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('FinOpsTimeEntries', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('timeEntries')) {
                const objectStore = db.createObjectStore('timeEntries', { 
                    keyPath: 'id', 
                    autoIncrement: true 
                });
                objectStore.createIndex('date', 'date', { unique: false });
                objectStore.createIndex('synced', 'synced', { unique: false });
            }
        };
    });
}

function getUnsyncedEntries(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['timeEntries'], 'readonly');
        const objectStore = transaction.objectStore('timeEntries');
        const index = objectStore.index('synced');
        const request = index.getAll(false);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function markAsSynced(db, entryId) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['timeEntries'], 'readwrite');
        const objectStore = transaction.objectStore('timeEntries');
        const request = objectStore.get(entryId);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => {
            const entry = request.result;
            if (entry) {
                entry.synced = true;
                const updateRequest = objectStore.put(entry);
                updateRequest.onerror = () => reject(updateRequest.error);
                updateRequest.onsuccess = () => resolve();
            } else {
                resolve();
            }
        };
    });
}

// Get CSRF token from cookies
function getCsrfToken() {
    const cookies = self.clients.matchAll().then(clients => {
        if (clients.length > 0) {
            return clients[0].postMessage({ type: 'GET_CSRF_TOKEN' });
        }
    });
    
    // Fallback: parse from cookie string
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie || '');
    const ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return Promise.resolve(c.substring(name.length, c.length));
        }
    }
    return Promise.resolve('');
}

// Message event
self.addEventListener('message', (event) => {
    console.log('[Service Worker] Message received:', event.data);
    
    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data.type === 'SYNC_REQUEST') {
        syncTimeEntries().catch(error => {
            console.error('[Service Worker] Manual sync failed:', error);
        });
    }
});

// Push notification event
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New update available',
        icon: '/images/icon-192x192.png',
        badge: '/images/badge-72x72.png',
        vibrate: [200, 100, 200]
    };
    
    event.waitUntil(
        self.registration.showNotification('FinOps Billing', options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/field/offline-time-entry')
    );
});
