const CACHE_NAME = "school-india-junior-v2";

/* Core files to cache */
const STATIC_ASSETS = [
    "/",
    "/index.php",
    "/login.php",
    "/manifest.json",
    "/public/images/logo.png"
];

/* Install */
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

/* Activate */
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

/* Fetch */
self.addEventListener("fetch", (event) => {

    // Always try network first for PHP pages
    if (event.request.url.includes(".php")) {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, response.clone());
                        return response;
                    });
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Cache-first strategy for others
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            return (
                cachedResponse ||
                fetch(event.request).then((networkResponse) => {
                    return caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                }).catch(() => {
                    if (event.request.mode === "navigate") {
                        return caches.match("/login.php");
                    }
                })
            );
        })
    );
});
