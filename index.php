<?php
session_start();
if (isset($_SESSION['login'])) {
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    
    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Agunan Capture">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="description" content="Sistem Input Data Agunan Mobile untuk Bank KPNO">
    
    <!-- Force fullscreen on Android -->
    <meta name="mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="assets/icon-192.svg">
    <link rel="apple-touch-icon" href="assets/icon-192.svg">
    
    <title>📱 Login Agunan Capture</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Reset dan Base Styles untuk Mobile */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-text-size-adjust: 100%;
            -moz-text-size-adjust: 100%;
        }

        .mobile-login-container {
            background: #fff;
            border-radius: 20px;
            padding: 32px 24px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            animation: slideUp 0.6s ease-out;
            border: 1px solid #ddd;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .app-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .app-icon {
            font-size: 64px;
            margin-bottom: 16px;
            display: block;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-8px);
            }
            60% {
                transform: translateY(-4px);
            }
        }

        .app-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .app-subtitle {
            font-size: 16px;
            color: #7f8c8d;
            opacity: 0.8;
        }

        .mobile-error {
            background: #fee;
            border: 2px solid #fcc;
            color: #d32f2f;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            text-align: center;
            animation: shake 0.5s ease-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .mobile-form-group {
            margin-bottom: 24px;
        }

        .mobile-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .mobile-input {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 16px; /* Prevent zoom on iOS */
            transition: all 0.3s ease;
            background: #fff;
            -webkit-appearance: none;
            appearance: none;
        }

        .mobile-input:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #7f8c8d;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .password-toggle:active {
            transform: translateY(-50%) scale(0.9);
        }

        .mobile-login-btn {
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            min-height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .mobile-login-btn:active {
            background: #1d4ed8;
        }

        .mobile-login-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .mobile-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }

        .mobile-version {
            font-size: 12px;
            color: #95a5a6;
            opacity: 0.8;
        }

        .mobile-features {
            margin-top: 20px;
        }

        .mobile-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #666;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            body {
                padding: 16px;
            }
            
            .mobile-login-container {
                padding: 24px 20px;
                border-radius: 16px;
            }
            
            .app-icon {
                font-size: 48px;
            }
            
            .app-title {
                font-size: 24px;
            }
            
            .app-subtitle {
                font-size: 14px;
            }
            
            .mobile-input {
                padding: 14px 16px;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 360px) {
            .mobile-login-container {
                padding: 20px 16px;
            }
            
            .app-title {
                font-size: 22px;
            }
        }

        /* Landscape Mode */
        @media (orientation: landscape) and (max-height: 600px) {
            body {
                padding: 12px;
            }
            
            .mobile-login-container {
                padding: 20px;
            }
            
            .app-header {
                margin-bottom: 20px;
            }
            
            .mobile-form-group {
                margin-bottom: 16px;
            }
        }

        /* iOS Safari Specific Fixes */
        @supports (-webkit-touch-callout: none) {
            .mobile-input {
                -webkit-appearance: none;
                appearance: none;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .mobile-login-container {
                background: rgba(30,30,30,0.95);
                color: white;
                border-color: #404040;
            }
            
            .app-title {
                color: white;
            }
            
            .app-subtitle {
                color: #e1e8ed;
            }
            
            .mobile-label {
                color: #e1e8ed;
            }
            
            .mobile-input {
                background: rgba(60,60,60,0.5);
                border-color: #555;
                color: white;
            }
            
            .mobile-input:focus {
                background: rgba(80,80,80,0.8);
            }
            
            .mobile-feature {
                color: #ccc;
            }
        }

        /* High DPI Displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .mobile-login-container {
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
        }

        /* Safe Area for iPhone X and newer */
        @supports (padding: max(0px)) {
            body {
                padding-left: max(20px, env(safe-area-inset-left));
                padding-right: max(20px, env(safe-area-inset-right));
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>
    <div class="mobile-login-container">
        <div class="app-header">
            <span class="app-icon">🏦📱</span>
            <h1 class="app-title">Agunan Capture</h1>
            <p class="app-subtitle">Sistem Input Data Agunan Mobile</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="mobile-error">
                ❌ <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login_proses.php">
            <div class="mobile-form-group">
                <label class="mobile-label" for="username">👤 Username</label>
                <input type="text" class="mobile-input" id="username" name="username" 
                       required autofocus autocomplete="username"
                       placeholder="Masukkan username Anda">
            </div>

            <div class="mobile-form-group">
                <label class="mobile-label" for="password">🔒 Password</label>
                <div class="password-wrapper">
                    <input type="password" class="mobile-input" id="password" name="password" 
                           required autocomplete="current-password"
                           placeholder="Masukkan password Anda">
                    <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">👁️</button>
                </div>
            </div>

            <button type="submit" class="mobile-login-btn" id="loginBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="btnText">🚀 Masuk ke Sistem</span>
            </button>
        </form>

        <div class="mobile-footer">
            <div class="mobile-features">
                <div class="mobile-feature">📱 <span>Optimized untuk Mobile</span></div>
                <div class="mobile-feature">📸 <span>Camera Ready</span></div>
                <div class="mobile-feature">⚡ <span>Fast & Responsive</span></div>
                <div class="mobile-feature">🔒 <span>Secure Login</span></div>
            </div>
            <div class="mobile-version">v2.0 Mobile Edition</div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
            
            // Haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        }

        // Form submission dengan loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('loadingSpinner');
            
            // Show loading state
            loginBtn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Memproses Login...';
            
            // Haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }
        });

        // Auto-clear error message
        const errorMsg = document.querySelector('.mobile-error');
        if (errorMsg) {
            setTimeout(() => {
                errorMsg.style.animation = 'slideUp 0.3s ease-out reverse';
                setTimeout(() => {
                    errorMsg.remove();
                }, 300);
            }, 5000);
        }

        // Mobile optimizations
        document.addEventListener('DOMContentLoaded', function() {
            // iOS input zoom prevention
            if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                const inputs = document.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.fontSize = '16px';
                    });
                });
            }
            
            // Touch feedback
            document.addEventListener('touchstart', function(e) {
                if (e.target.matches('button, .mobile-input')) {
                    e.target.style.transform = 'scale(0.98)';
                    e.target.style.transition = 'transform 0.1s ease';
                }
            });
            
            document.addEventListener('touchend', function(e) {
                if (e.target.matches('button, .mobile-input')) {
                    setTimeout(() => {
                        e.target.style.transform = 'scale(1)';
                    }, 100);
                }
            });
            
            // Auto-focus username pada mobile
            if (window.innerWidth <= 768) {
                setTimeout(() => {
                    document.getElementById('username').focus();
                }, 500);
            }
        });

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(registration => {
                        console.log('✅ Service Worker registered:', registration.scope);
                    })
                    .catch(error => {
                        console.log('❌ Service Worker registration failed:', error);
                    });
            });
        }

        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('💾 PWA installable');
            
            // Optional: Show custom install button
            // You can add UI here to prompt user to install
        });

        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA installed successfully');
            deferredPrompt = null;
        });

        // Network status
        window.addEventListener('online', function() {
            console.log('Online');
        });
        
        window.addEventListener('offline', function() {
            console.log('Offline');
        });
    </script>
</body>
</html>