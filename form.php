<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}
$nama_kc = $_SESSION['nama_kc'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <title>Input Data Agunan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
        }

        .header {
            background: #3475ca;
            color: white;
            padding: 12px 16px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .user-info {
            font-size: 14px;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 12px;
        }

        .container {
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-input:focus {
            outline: none;
            border-color: #3475ca;
        }

        .foto-item {
            background: #f9f9f9;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            position: relative;
        }

        .foto-item.has-file {
            border-color: #28a745;
            background: #f8fff8;
        }

        .file-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .add-photo-btn {
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin: 16px 0;
        }

        .add-photo-btn:active {
            background: #218838;
        }

        .submit-btn {
            width: 100%;
            background: #3475ca;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 4px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-btn:active {
            background: #2a5ca0;
        }

        .remove-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 8px;
            }
            
            .user-info {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                font-size: 12px;
            }
            
            .container {
                padding: 12px;
            }
            
            .form-card {
                padding: 16px;
            }
            
            .form-input,
            .file-input {
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 10px 12px;
            }
            
            .header-title {
                font-size: 16px;
            }
            
            .container {
                padding: 8px;
            }
            
            .form-card {
                padding: 12px;
                border-radius: 6px;
            }
            
            .foto-item {
                padding: 12px;
            }
        }

        /* iOS Safari fixes */
        @supports (-webkit-touch-callout: none) {
            .form-input,
            .file-input {
                -webkit-appearance: none;
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-title">Input Data Agunan</div>
            <div class="user-info">
                <span>User: <?= htmlspecialchars($username) ?> | KC: <?= htmlspecialchars($nama_kc) ?></span>
                <a href="logout.php"><button class="logout-btn">Logout</button></a>
            </div>
        </div>
    </div>

    <div class="container">
        <form method="POST" action="proses.php" enctype="multipart/form-data" id="agunanForm">
            <div class="form-card">
                <div class="form-group">
                    <label class="form-label">ID Agunan</label>
                    <input type="text" class="form-input" name="id_agunan" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Nasabah</label>
                    <input type="text" class="form-input" name="nama_nasabah" required>
                </div>

                <div class="form-group">
                    <label class="form-label">No. Rekening Kredit</label>
                    <input type="text" class="form-input" name="no_rek" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Agunan & Keterangan</label>
                    <div id="foto-list">
                        <div class="foto-item">
                            <input type="file" class="file-input" name="foto[]" accept="image/*" 
                                   capture="environment" required onchange="handleFileSelect(this)">
                            <input type="text" class="form-input" name="ket[]" 
                                   placeholder="Keterangan foto" required>
                        </div>
                    </div>
                    <button type="button" class="add-photo-btn" onclick="addFoto()">+ Tambah Foto</button>
                </div>

                <button type="submit" class="submit-btn">Simpan Data</button>
            </div>
        </form>
    </div>
    <script>
        let fotoIndex = 1;

        // Mobile-optimized functions
        function addFoto() {
            const fotoList = document.getElementById('foto-list');
            const fotoDiv = document.createElement('div');
            fotoDiv.className = 'foto-item';
            fotoDiv.setAttribute('data-index', fotoIndex);
            
            fotoDiv.innerHTML = `
                <button type="button" class="remove-btn" onclick="removeFoto(this)" aria-label="Hapus foto">✕</button>
                <div class="file-input-wrapper">
                    <input type="file" class="file-input" name="foto[]" accept="image/*" 
                           capture="environment" required onchange="handleFileSelect(this)"
                           aria-label="Pilih foto agunan">
                </div>
                <input type="text" class="form-input" name="ket[]" 
                       placeholder="Keterangan foto (contoh: Tampak samping rumah)" required
                       aria-label="Keterangan foto">
            `;
            
            fotoList.appendChild(fotoDiv);
            fotoIndex++;
            
            // Smooth scroll dengan mobile optimization
            setTimeout(() => {
                fotoDiv.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'nearest'
                });
            }, 100);
            
            // Haptic feedback untuk mobile
            vibrate(50);
        }

        function removeFoto(button) {
            const fotoItem = button.closest('.foto-item');
            const fotoList = document.getElementById('foto-list');
            
            if (fotoList.children.length > 1) {
                // Animasi fade out
                fotoItem.style.transform = 'scale(0.9)';
                fotoItem.style.opacity = '0';
                fotoItem.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    fotoItem.remove();
                }, 300);
                
                vibrate(30);
            } else {
                // Mobile-friendly alert
                showMobileAlert('Minimal harus ada satu foto agunan!', 'warning');
            }
        }

        function handleFileSelect(input) {
            const fotoItem = input.closest('.foto-item');
            
            if (input.files && input.files[0]) {
                fotoItem.classList.add('has-file');
                
                // Preview image untuk mobile
                createImagePreview(input, fotoItem);
                
                // Auto-focus ke keterangan dengan delay
                const ketInput = fotoItem.querySelector('input[name="ket[]"]');
                if (ketInput) {
                    setTimeout(() => {
                        ketInput.focus();
                        // Scroll ke input jika perlu
                        ketInput.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }, 500);
                }
                
                vibrate(100);
            } else {
                fotoItem.classList.remove('has-file');
                removeImagePreview(fotoItem);
            }
        }

        function createImagePreview(input, fotoItem) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remove existing preview
                    const existingPreview = fotoItem.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Create new preview
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" 
                             style="width: 100%; max-height: 200px; object-fit: cover; 
                                    border-radius: 8px; margin: 12px 0;">
                        <p style="font-size: 12px; color: #666; text-align: center;">
                            ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                        </p>
                    `;
                    
                    const wrapper = fotoItem.querySelector('.file-input-wrapper');
                    wrapper.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        }

        function removeImagePreview(fotoItem) {
            const preview = fotoItem.querySelector('.image-preview');
            if (preview) {
                preview.remove();
            }
        }

        // Mobile-friendly alert system
        function showMobileAlert(message, type) {
            if (!type) type = 'info';
            const alert = document.createElement('div');
            alert.className = 'mobile-alert mobile-alert-' + type;
            alert.style.cssText = 'position: fixed; top: 20px; left: 16px; right: 16px; z-index: 10000; padding: 16px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); animation: slideDown 0.3s ease-out; font-weight: 500; text-align: center;';
            
            if (type === 'warning') {
                alert.style.background = '#fff3cd';
                alert.style.border = '1px solid #ffeaa7';
                alert.style.color = '#856404';
            } else if (type === 'error') {
                alert.style.background = '#f8d7da';
                alert.style.border = '1px solid #f5c6cb';
                alert.style.color = '#721c24';
            } else {
                alert.style.background = '#cce7ff';
                alert.style.border = '1px solid #99d6ff';
                alert.style.color = '#004085';
            }
            
            alert.textContent = message;
            document.body.appendChild(alert);
            
            // Auto remove
            setTimeout(() => {
                alert.style.animation = 'slideUp 0.3s ease-out';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 300);
            }, 3000);
        }

        // Enhanced form validation
        document.getElementById('agunanForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInputs = document.querySelectorAll('input[type="file"]');
            const ketInputs = document.querySelectorAll('input[name="ket[]"]');
            const submitBtn = document.querySelector('.submit-btn');
            
            let isValid = true;
            let errorMessage = '';
            
            // Validate required fields
            const requiredFields = [
                { element: document.querySelector('input[name="id_agunan"]'), name: 'ID Agunan' },
                { element: document.querySelector('input[name="nama_nasabah"]'), name: 'Nama Nasabah' },
                { element: document.querySelector('input[name="no_rek"]'), name: 'No. Rekening' }
            ];
            
            requiredFields.forEach(field => {
                if (!field.element.value.trim()) {
                    isValid = false;
                    errorMessage = `${field.name} harus diisi!`;
                    field.element.focus();
                    return;
                }
            });
            
            if (!isValid) {
                showMobileAlert(errorMessage, 'error');
                return;
            }
            
            // Validate files
            for (let i = 0; i < fileInputs.length; i++) {
                if (!fileInputs[i].files || !fileInputs[i].files[0]) {
                    isValid = false;
                    errorMessage = `Foto ${i + 1} belum dipilih!`;
                    fileInputs[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    break;
                }
            }
            
            if (!isValid) {
                showMobileAlert(errorMessage, 'error');
                return;
            }
            
            // Validate descriptions
            for (let i = 0; i < ketInputs.length; i++) {
                if (!ketInputs[i].value.trim()) {
                    isValid = false;
                    errorMessage = `Keterangan foto ${i + 1} harus diisi!`;
                    ketInputs[i].focus();
                    ketInputs[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    break;
                }
            }
            
            if (!isValid) {
                showMobileAlert(errorMessage, 'error');
                return;
            }
            
            // Show loading state dengan animasi
            submitBtn.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <div style="width: 20px; height: 20px; border: 2px solid transparent; 
                                border-top: 2px solid white; border-radius: 50%; 
                                animation: spin 1s linear infinite;"></div>
                    Menyimpan Data...
                </div>
            `;
            submitBtn.disabled = true;
            
            // Add loading CSS animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                @keyframes slideDown {
                    from { transform: translateY(-100%); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(0); opacity: 1; }
                    to { transform: translateY(-100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
            
            // Submit form
            setTimeout(() => {
                this.submit();
            }, 1000);
        });

        // Mobile optimization functions
        function vibrate(duration = 50) {
            if (navigator.vibrate) {
                navigator.vibrate(duration);
            }
        }

        function isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Initialize mobile optimizations
        document.addEventListener('DOMContentLoaded', function() {
            // iOS specific fixes
            if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
                // Prevent zoom on input focus
                const inputs = document.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.fontSize = '16px';
                    });
                });
            }
            
            // Add mobile class untuk CSS targeting
            if (isMobile()) {
                document.body.classList.add('mobile-device');
            }
            
            // Touch feedback untuk semua button
            document.addEventListener('touchstart', function(e) {
                if (e.target.matches('button, .file-input, .form-input')) {
                    e.target.style.transform = 'scale(0.98)';
                    e.target.style.transition = 'transform 0.1s ease';
                }
            });
            
            document.addEventListener('touchend', function(e) {
                if (e.target.matches('button, .file-input, .form-input')) {
                    setTimeout(() => {
                        e.target.style.transform = 'scale(1)';
                    }, 100);
                }
            });
            
            // Auto-save form data ke localStorage
            const form = document.getElementById('agunanForm');
            const inputs = form.querySelectorAll('input[type="text"]');
            
            inputs.forEach(input => {
                // Load saved data
                const savedValue = localStorage.getItem('agunan_' + input.name);
                if (savedValue) {
                    input.value = savedValue;
                }
                
                // Save on input
                input.addEventListener('input', function() {
                    localStorage.setItem('agunan_' + this.name, this.value);
                });
            });
            
            // Clear saved data on successful submit
            form.addEventListener('submit', function() {
                setTimeout(() => {
                    inputs.forEach(input => {
                        localStorage.removeItem('agunan_' + input.name);
                    });
                }, 2000);
            });
        });

        let fotoIndex = 1;

        function addFoto() {
            const fotoList = document.getElementById('foto-list');
            const fotoDiv = document.createElement('div');
            fotoDiv.className = 'foto-item';
            
            fotoDiv.innerHTML = 
                '<button type="button" class="remove-btn" onclick="removeFoto(this)">Hapus</button>' +
                '<input type="file" class="file-input" name="foto[]" accept="image/*" capture="environment" required onchange="handleFileSelect(this)">' +
                '<input type="text" class="form-input" name="ket[]" placeholder="Keterangan foto" required>';
            
            fotoList.appendChild(fotoDiv);
            fotoIndex++;
        }

        function removeFoto(button) {
            const fotoItem = button.closest('.foto-item');
            const fotoList = document.getElementById('foto-list');
            
            if (fotoList.children.length > 1) {
                fotoItem.remove();
            } else {
                alert('Minimal harus ada satu foto agunan!');
            }
        }

        function handleFileSelect(input) {
            const fotoItem = input.closest('.foto-item');
            if (input.files && input.files[0]) {
                fotoItem.classList.add('has-file');
                
                // Auto-focus to description field
                const ketInput = fotoItem.querySelector('input[name="ket[]"]');
                if (ketInput) {
                    setTimeout(function() {
                        ketInput.focus();
                    }, 100);
                }
            } else {
                fotoItem.classList.remove('has-file');
            }
        }

        // Simple form validation
        document.getElementById('agunanForm').addEventListener('submit', function(e) {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            const ketInputs = document.querySelectorAll('input[name="ket[]"]');
            
            let hasEmptyFile = false;
            let hasEmptyKet = false;
            
            for (let i = 0; i < fileInputs.length; i++) {
                if (!fileInputs[i].files || !fileInputs[i].files[0]) {
                    hasEmptyFile = true;
                    break;
                }
            }
            
            for (let i = 0; i < ketInputs.length; i++) {
                if (!ketInputs[i].value.trim()) {
                    hasEmptyKet = true;
                    break;
                }
            }
            
            if (hasEmptyFile) {
                e.preventDefault();
                alert('Harap pilih semua file foto yang diperlukan!');
                return;
            }
            
            if (hasEmptyKet) {
                e.preventDefault();
                alert('Harap isi semua keterangan foto!');
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.textContent = 'Menyimpan Data...';
            submitBtn.disabled = true;
        });

        // iOS zoom prevention
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            const inputs = document.querySelectorAll('input');
            for (let i = 0; i < inputs.length; i++) {
                inputs[i].addEventListener('focus', function() {
                    this.style.fontSize = '16px';
                });
            }
        }
    </script>
</body>
</html>