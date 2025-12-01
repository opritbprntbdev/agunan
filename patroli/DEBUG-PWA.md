# DEBUG PWA DI CHROME MOBILE

## Cara 1: Chrome Remote Debugging (RECOMMENDED)
1. **Di HP Android:**
   - Buka Chrome → Settings → Developer options
   - Enable "USB Debugging"
   - Sambungkan HP ke laptop via USB

2. **Di Laptop/PC:**
   - Buka Chrome
   - Ketik di address bar: `chrome://inspect/#devices`
   - HP akan muncul di list
   - Klik "Inspect" pada tab PWA yang terbuka
   - Console error akan muncul di laptop!

## Cara 2: View Source di Chrome Mobile
1. Buka PWA di Chrome mobile
2. Address bar ketik: `view-source:https://your-url.com/patroli/...`
3. Lihat error PHP di source code

## Cara 3: Enable Error Log di PHP
Aktifkan error_log di file PHP:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Cara 4: Cek Error Log File
1. Cek file: `C:\wamp64\logs\php_error.log`
2. Atau di folder project: `error_log`

## Cara 5: Test di Browser Desktop Dulu
1. Buka di Chrome Desktop: http://localhost/agunan-capture/patroli/ui/patroli_manage_room.php
2. Buka DevTools (F12) → Console
3. Test semua fitur, lihat error

## Cara 6: Ajax Response Debug
Tambahkan alert() di JavaScript untuk debug response:
```javascript
console.log('Response:', result);
alert(JSON.stringify(result));
```
