const CACHE_NAME = 'cou-youth-pwa-v1';
const STATIC_EXTENSIONS = /\.(?:css|js|png|jpg|jpeg|webp|svg|gif|ico|woff2?|ttf|map)$/i;

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => new Response(
                '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><style>body{font-family:Arial,sans-serif;margin:0;background:#f6f7fb;color:#182230;display:grid;place-items:center;min-height:100vh;padding:24px}.card{max-width:520px;background:#fff;border:1px solid #e7e9f0;border-radius:18px;padding:28px;box-shadow:0 12px 34px rgba(16,24,40,.08)}h1{margin-top:0}p{line-height:1.6;color:#667085}</style></head><body><div class="card"><h1>You are offline</h1><p>The COU Youth Platform needs an internet connection to load this page. Reconnect and try again.</p></div></body></html>',
                { headers: { 'Content-Type': 'text/html; charset=UTF-8' } }
            ))
        );

        return;
    }

    if (!STATIC_EXTENSIONS.test(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }

                return response;
            });
        })
    );
});
