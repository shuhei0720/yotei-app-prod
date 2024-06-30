self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open('your-app-cache').then(function(cache) {
            return cache.addAll([
                '/',
                '/css/app.css',
                '/js/app.js',
                '/manifest.json',
                '/path/to/icon-192x192.png',
                '/path/to/icon-512x512.png'
            ]);
        })
    );
});

self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request).then(function(response) {
            return response || fetch(event.request);
        })
    );
});