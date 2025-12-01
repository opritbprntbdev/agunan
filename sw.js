// Service Worker untuk PWA - Minimal untuk fast install
const CACHE_NAME = 'agunan-capture-v3';
const urlsToCache = [
  // Minimal cache - hanya icon untuk fast install
];

// Install event - cache minimal files
self.addEventListener("install", (event) => {
  console.log('SW: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('SW: Cache opened');
      return cache.addAll(urlsToCache);
    })
  );
  // Skip waiting untuk langsung aktif
  self.skipWaiting();
});

// Fetch event - network first (no caching for faster performance)
self.addEventListener('fetch', event => {
  // Skip caching - always fetch from network for fastest response
  // This makes PWA work like a normal website but with install capability
  return;
});

// Activate event - cleanup old caches
self.addEventListener("activate", (event) => {
  console.log('SW: Activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log("SW: Deleting old cache:", cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // Take control immediately
  return self.clients.claim();
});
