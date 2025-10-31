// Service Worker untuk PWA
const CACHE_NAME = 'agunan-capture-v2'; // Bumped version to force update
const urlsToCache = [
  '/agunan-capture/assets/css/style.css',
  '/agunan-capture/assets/css/mobile.css'
];

// Install event - cache files
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then((cache) => {
        console.log("Cache opened");
        return cache.addAll(
          urlsToCache.map((url) => new Request(url, { cache: "reload" }))
        );
      })
      .catch((err) => {
        console.log("Cache install error:", err);
      })
  );
  self.skipWaiting();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // SKIP ALL PHP FILES - only cache static assets
  if (url.pathname.endsWith('.php') || 
      url.pathname.includes('/process/') ||
      url.pathname.includes('/ui/') ||
      url.pathname === '/agunan-capture/' ||
      url.pathname === '/agunan-capture/index.php') {
    // Let browser handle PHP pages directly (no caching, no intercepting)
    return;
  }

  // Only cache static files (CSS, JS, images)
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        
        return fetch(event.request).then(response => {
          // Only cache successful responses for static files
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        });
      })
      .catch(() => {
        // Network failed, return cached or error
        return new Response('Offline', { status: 503 });
      })
  );
});

// Activate event - cleanup old caches
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log("Deleting old cache:", cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});
