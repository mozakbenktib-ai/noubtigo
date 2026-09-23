const CACHE_NAME = 'noubtigo-v1';
const OFFLINE_URL = '/offline';

const ASSETS_TO_CACHE = [
    '/',
    OFFLINE_URL,
    '/frontend/css/style.css',
    '/icons/icon-512x512.png',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cairo:wght@400;600;700&display=swap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only cache GET requests
    if (event.request.method !== 'GET') return;

    // Avoid caching dynamic/sensitive paths
    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/api') || url.pathname.startsWith('/admin') || url.pathname.includes('logout')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .catch(() => {
                return caches.match(event.request).then((response) => {
                    if (response) {
                        return response;
                    }
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});
