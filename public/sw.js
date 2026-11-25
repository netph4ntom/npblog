const CACHE_VERSION = 'v2';
const PRECACHE_NAME = `npblog-precache-${CACHE_VERSION}`;
const PAGES_CACHE = `npblog-pages-${CACHE_VERSION}`;
const ASSETS_CACHE = `npblog-assets-${CACHE_VERSION}`;
const ACTIVE_CACHES = new Set([PRECACHE_NAME, PAGES_CACHE, ASSETS_CACHE]);

const PRECACHE_URLS = [
    '/offline',
    '/manifest.webmanifest',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/fonts/caveat-700.woff2',
    '/fonts/patrick-hand-400.woff2',
    '/fonts/dm-sans-400.woff2',
    '/fonts/dm-sans-700.woff2',
    '/fonts/jetbrains-mono-400.woff2',
];

const BYPASS_PREFIXES = [
    '/admin',
    '/dashboard',
    '/login',
    '/logout',
    '/profile',
    '/forgot-password',
    '/reset-password',
    '/confirm-password',
    '/verify-email',
];

function isBypassedPath(pathname) {
    return BYPASS_PREFIXES.some(
        (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`)
    );
}

function isImmutableAsset(pathname) {
    return (
        pathname.startsWith('/build/') ||
        pathname.startsWith('/fonts/') ||
        pathname.startsWith('/icons/')
    );
}

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(PRECACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => !ACTIVE_CACHES.has(key))
                        .map((key) => caches.delete(key))
                )
            )
            .then(() => self.clients.claim())
    );
});

async function handleNavigationRequest(request) {
    const cache = await caches.open(PAGES_CACHE);
    try {
        const response = await fetch(request);
        const contentType = response.headers.get('content-type') || '';
        if (
            response.ok &&
            response.type === 'basic' &&
            !response.redirected &&
            contentType.includes('text/html')
        ) {
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cachedPage = await cache.match(request);
        if (cachedPage) {
            return cachedPage;
        }
        const offlinePage = await caches.match('/offline');
        return offlinePage || Response.error();
    }
}

async function handleCacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    const response = await fetch(request);
    if (response.ok && response.type === 'basic') {
        const cache = await caches.open(ASSETS_CACHE);
        cache.put(request, response.clone());
    }
    return response;
}

async function handleStaleWhileRevalidate(request) {
    const cache = await caches.open(ASSETS_CACHE);
    const cached = await cache.match(request);
    const networkFetch = fetch(request)
        .then((response) => {
            if (response.ok && response.type === 'basic') {
                cache.put(request, response.clone());
            }
            return response;
        })
        .catch(() => cached);

    return cached || networkFetch;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || isBypassedPath(url.pathname)) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(handleNavigationRequest(request));
        return;
    }

    if (isImmutableAsset(url.pathname)) {
        event.respondWith(handleCacheFirst(request));
        return;
    }

    if (
        url.pathname === '/manifest.webmanifest' ||
        url.pathname.startsWith('/storage/')
    ) {
        event.respondWith(handleStaleWhileRevalidate(request));
    }
});

