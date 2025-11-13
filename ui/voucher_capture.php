<?php
session_start();
require_once '../config.php';

// Check login
if (!isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

$username = $_SESSION['username'];
$kode_kantor = $_SESSION['kode_kantor'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Capture - BPR</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .voucher-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }

        .camera-section {
            display: none;
            text-align: center;
        }

        #video {
            width: 100%;
            max-width: 500px;
            border: 2px solid #333;
            border-radius: 8px;
            margin: 20px 0;
        }

        #canvas {
            display: none;
        }

        .capture-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 50%;
            cursor: pointer;
            margin: 10px;
            width: 70px;
            height: 70px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }

        .capture-button:active {
            transform: scale(0.95);
        }

        .add-photo-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
        }

        .photo-gallery {
            display: none;
            margin-top: 20px;
        }

        .photo-item {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            position: relative;
        }

        .photo-item img {
            width: 100%;
            max-width: 300px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .photo-item input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .delete-photo-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .start-camera-button,
        .save-all-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 10px;
            display: inline-block;
        }

        .save-all-button {
            background: #28a745;
            width: 100%;
            margin-top: 20px;
        }

        .photo-counter {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }

        .control-buttons {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📸 Voucher Capture</h1>
        <div class="user-info">
            User: <?= htmlspecialchars($username) ?> | Cabang: <?= $kode_kantor ?>
        </div>
    </div>

    <div class="voucher-container">
        <!-- Start Camera Button -->
        <div id="startSection" class="text-center">
            <h2>Capture Voucher Pengeluaran</h2>
            <p>Klik tombol di bawah untuk mulai capture foto voucher</p>
            <button class="start-camera-button" onclick="startCamera()">
                📷 Mulai Camera
            </button>
            <br><br>
            <a href="../form.php" class="btn btn-secondary">← Kembali</a>
        </div>

        <!-- Camera Section -->
        <div id="cameraSection" class="camera-section">
            <h3>Ambil Foto Voucher</h3>
            <div class="photo-counter">
                Foto yang sudah diambil: <span id="photoCount">0</span>
            </div>
            
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas"></canvas>
            
            <div class="control-buttons">
                <button class="capture-button" onclick="capturePhoto()" title="Ambil Foto">
                    📷
                </button>
            </div>

            <button class="add-photo-button" onclick="stopCamera()" style="display:none;" id="doneButton">
                ✅ Selesai Capture
            </button>
        </div>

        <!-- Photo Gallery & Review -->
        <div id="photoGallery" class="photo-gallery"></div>

        <!-- Save Button -->
        <div id="saveSection" style="display:none;">
            <button class="save-all-button" onclick="saveAllPhotos()">
                💾 Simpan Semua Foto
            </button>
        </div>
    </div>

    <script>
        let stream = null;
        let capturedPhotos = []; // Array to store {dataURL, keterangan}
        let photoCounter = 0;

        async function startCamera() {
            try {
                // Hide start section, show camera
                document.getElementById('startSection').style.display = 'none';
                document.getElementById('cameraSection').style.display = 'block';

                // Get camera stream
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1920 },
                        height: { ideal: 1080 }
                    } 
                });
                
                const video = document.getElementById('video');
                video.srcObject = stream;
                
                console.log('Camera started successfully');
            } catch (error) {
                console.error('Error starting camera:', error);
                alert('Gagal mengakses kamera. Pastikan Anda memberikan izin akses kamera.\n\nError: ' + error.message);
                document.getElementById('startSection').style.display = 'block';
                document.getElementById('cameraSection').style.display = 'none';
            }
        }

        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Set canvas size to video size
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Get image data URL
            const dataURL = canvas.toDataURL('image/jpeg', 0.8);

            // Add to array
            photoCounter++;
            capturedPhotos.push({
                id: photoCounter,
                dataURL: dataURL,
                keterangan: ''
            });

            // Update UI
            updatePhotoCount();
            renderPhotoGallery();

            // Show done button after first photo
            document.getElementById('doneButton').style.display = 'inline-block';

            // Flash effect
            canvas.style.display = 'block';
            setTimeout(() => {
                canvas.style.display = 'none';
            }, 100);

            console.log('Photo captured:', photoCounter);
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            // Hide camera, show gallery
            document.getElementById('cameraSection').style.display = 'none';
            document.getElementById('photoGallery').style.display = 'block';
            document.getElementById('saveSection').style.display = 'block';

            console.log('Camera stopped');
        }

        function updatePhotoCount() {
            document.getElementById('photoCount').textContent = capturedPhotos.length;
        }

        function renderPhotoGallery() {
            const gallery = document.getElementById('photoGallery');
            gallery.innerHTML = '<h3>Review Foto yang Diambil</h3>';

            capturedPhotos.forEach((photo, index) => {
                const photoItem = document.createElement('div');
                photoItem.className = 'photo-item';
                photoItem.innerHTML = `
                    <button class="delete-photo-button" onclick="deletePhoto(${index})">🗑️ Hapus</button>
                    <div style="text-align: center;">
                        <strong>Foto ${index + 1}</strong>
                        <br>
                        <img src="${photo.dataURL}" alt="Foto ${index + 1}">
                    </div>
                    <label>Keterangan Foto:</label>
                    <input type="text" 
                           placeholder="Masukkan keterangan foto..." 
                           value="${photo.keterangan}"
                           onchange="updateKeterangan(${index}, this.value)">
                `;
                gallery.appendChild(photoItem);
            });

            // Add button to add more photos
            if (capturedPhotos.length > 0) {
                const addMoreButton = document.createElement('button');
                addMoreButton.className = 'add-photo-button';
                addMoreButton.style.width = '100%';
                addMoreButton.textContent = '➕ Tambah Foto Lagi';
                addMoreButton.onclick = addMorePhotos;
                gallery.appendChild(addMoreButton);
            }

            gallery.style.display = 'block';
        }

        function deletePhoto(index) {
            if (confirm('Hapus foto ini?')) {
                capturedPhotos.splice(index, 1);
                renderPhotoGallery();
                updatePhotoCount();

                // If no photos left, hide gallery and save button
                if (capturedPhotos.length === 0) {
                    document.getElementById('photoGallery').style.display = 'none';
                    document.getElementById('saveSection').style.display = 'none';
                    document.getElementById('startSection').style.display = 'block';
                }
            }
        }

        function updateKeterangan(index, value) {
            capturedPhotos[index].keterangan = value;
            console.log('Updated keterangan for photo', index + 1, ':', value);
        }

        function addMorePhotos() {
            // Hide gallery, show camera again
            document.getElementById('photoGallery').style.display = 'none';
            document.getElementById('saveSection').style.display = 'none';
            startCamera();
        }

        function saveAllPhotos() {
            if (capturedPhotos.length === 0) {
                alert('Tidak ada foto yang diambil!');
                return;
            }

            // Check if all photos have keterangan
            const missingKeterangan = capturedPhotos.filter(p => !p.keterangan.trim());
            if (missingKeterangan.length > 0) {
                if (!confirm(`${missingKeterangan.length} foto belum memiliki keterangan. Lanjutkan simpan?`)) {
                    return;
                }
            }

            console.log('Saving photos:', capturedPhotos);

            // TODO: Implement upload to server
            // For now, just show success message
            alert(`✅ Berhasil!\n\nTotal foto: ${capturedPhotos.length}\n\n(Upload ke server akan diimplementasikan setelah database schema selesai)`);

            // Show data in console for debugging
            capturedPhotos.forEach((photo, index) => {
                console.log(`Foto ${index + 1}:`, {
                    size: photo.dataURL.length,
                    keterangan: photo.keterangan
                });
            });

            // Reset
            if (confirm('Capture voucher baru?')) {
                resetAll();
            } else {
                window.location.href = '../form.php';
            }
        }

        function resetAll() {
            capturedPhotos = [];
            photoCounter = 0;
            document.getElementById('photoGallery').style.display = 'none';
            document.getElementById('saveSection').style.display = 'none';
            document.getElementById('cameraSection').style.display = 'none';
            document.getElementById('startSection').style.display = 'block';
            updatePhotoCount();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>
