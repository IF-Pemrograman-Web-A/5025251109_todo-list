const STATIC_CACHE = 'todo-shell-v1';
const PAGE_CACHE = 'todo-pages-v1';
const IMAGE_CACHE = 'todo-images-v1';

const APP_SHELL = [
    './',
    'index.php',
    'index.php?menu=personal',
    'style.css',
    'script.js',
    'manifest.json',
    'offline.html',
    'icon-192.png',
    'icon-512.png'
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(STATIC_CACHE).then(function(cache) {
            return cache.addAll(APP_SHELL);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    const validCaches = [STATIC_CACHE, PAGE_CACHE, IMAGE_CACHE];
    event.waitUntil(
        caches.keys().then(function(keys) {
            return Promise.all(
                keys.filter(function(key) {
                    return !validCaches.includes(key);
                }).map(function(key) {
                    return caches.delete(key);
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', function(event) {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    if (event.request.mode === 'navigate' || url.pathname.endsWith('.php')) {
        event.respondWith(
            fetch(event.request).then(function(networkRes) {
                const clone = networkRes.clone();
                caches.open(PAGE_CACHE).then(function(cache) {
                    cache.put(event.request, clone);
                });
                return networkRes;
            }).catch(function() {
                return caches.match(event.request).then(function(cachedRes) {
                    return cachedRes || caches.match('offline.html');
                });
            })
        );
        return;
    }

    if (url.pathname.includes('/uploads/')) {
        event.respondWith(
            caches.open(IMAGE_CACHE).then(function(cache) {
                return cache.match(event.request).then(function(cachedRes) {
                    const fetchPromise = fetch(event.request).then(function(networkRes) {
                        cache.put(event.request, networkRes.clone());
                        return networkRes;
                    }).catch(function() {
                        return cachedRes;
                    });
                    return cachedRes || fetchPromise;
                });
            })
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then(function(cachedRes) {
            return cachedRes || fetch(event.request).then(function(networkRes) {
                const clone = networkRes.clone();
                caches.open(STATIC_CACHE).then(function(cache) {
                    cache.put(event.request, clone);
                });
                return networkRes;
            });
        })
    );
});
