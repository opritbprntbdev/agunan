<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">
    <title>📄 Document Scanner Demo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #1a1a1a;
            color: white;
            overflow-x: hidden;
        }

        .container {
            max-width: 100%;
            padding: 16px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
        }

        .btn {
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:active {
            background: #1d4ed8;
        }

        .btn.secondary {
            background: #10b981;
        }

        .btn:disabled {
            background: #666;
            cursor: not-allowed;
        }

        #cameraInput {
            display: none;
        }

        .canvas-container {
            position: relative;
            width: 100%;
            margin: 20px 0;
            background: #2a2a2a;
            border-radius: 12px;
            overflow: hidden;
            display: none;
        }

        canvas {
            display: block;
            width: 100%;
            height: auto;
            touch-action: none;
        }

        .corner-point {
            position: absolute;
            width: 30px;
            height: 30px;
            background: #2563eb;
            border: 3px solid white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            cursor: move;
            touch-action: none;
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }

        .corner-point.active {
            background: #10b981;
        }

        .instructions {
            background: #2a2a2a;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
            font-size: 14px;
            line-height: 1.6;
        }

        .instructions strong {
            color: #10b981;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 16px;
            color: #10b981;
        }

        .loading::after {
            content: '...';
            animation: dots 1.5s infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }

        .preview-container {
            display: none;
            margin: 20px 0;
        }

        .preview-title {
            font-size: 18px;
            margin-bottom: 12px;
            color: #10b981;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .action-buttons button {
            flex: 1;
        }

        .status {
            background: #10b981;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin: 16px 0;
            text-align: center;
            display: none;
        }

        .status.error {
            background: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 Document Scanner</h1>
            <button class="btn" onclick="window.location.href='../home.php'">« Back</button>
        </div>

        <div class="instructions">
            <strong>📸 Cara Pakai:</strong><br>
            1. Klik tombol "Ambil Foto"<br>
            2. Foto dokumen (pastikan semua sudut terlihat)<br>
            3. System otomatis detect tepi dokumen<br>
            4. Drag titik biru untuk adjust kalau perlu<br>
            5. Klik "Scan & Straighten" untuk hasil akhir
        </div>

        <div class="status" id="status"></div>

        <input type="file" id="cameraInput" accept="image/*" capture="environment">
        
        <button class="btn" id="captureBtn" onclick="takePhoto()">
            📸 Ambil Foto Dokumen
        </button>

        <div id="loadingIndicator" class="loading" style="display: none;">
            Loading OpenCV.js
        </div>

        <div class="canvas-container" id="canvasContainer">
            <canvas id="originalCanvas"></canvas>
            <div id="corners"></div>
        </div>

        <div class="action-buttons" id="actionButtons" style="display: none;">
            <button class="btn secondary" onclick="processDocument()">
                ✨ Scan & Straighten
            </button>
            <button class="btn" onclick="resetCamera()">
                🔄 Foto Ulang
            </button>
        </div>

        <div class="preview-container" id="previewContainer">
            <div class="preview-title">✅ Hasil Scan:</div>
            <canvas id="processedCanvas"></canvas>
            <div class="action-buttons">
                <button class="btn secondary" onclick="saveDocument()">
                    💾 Simpan
                </button>
                <button class="btn" onclick="resetCamera()">
                    🔄 Scan Ulang
                </button>
            </div>
        </div>
    </div>

    <!-- OpenCV.js CDN -->
    <script async src="https://docs.opencv.org/4.8.0/opencv.js" onload="onOpenCvReady()" onerror="onOpenCvError()"></script>

    <script>
        let cv;
        let originalImage;
        let detectedCorners = [];
        let isDragging = false;
        let draggedCornerIndex = -1;
        let canvasRect;

        // Show loading
        document.getElementById('loadingIndicator').style.display = 'block';
        document.getElementById('captureBtn').disabled = true;

        function onOpenCvReady() {
            cv = window.cv;
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('captureBtn').disabled = false;
            showStatus('✅ OpenCV siap! Silakan ambil foto dokumen.', false);
        }

        function onOpenCvError() {
            showStatus('❌ Gagal load OpenCV. Refresh halaman.', true);
        }

        function showStatus(message, isError = false) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status' + (isError ? ' error' : '');
            status.style.display = 'block';
            setTimeout(() => {
                status.style.display = 'none';
            }, 3000);
        }

        function takePhoto() {
            document.getElementById('cameraInput').click();
        }

        document.getElementById('cameraInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            showStatus('📸 Memproses foto...', false);

            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    processImage(img);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        function processImage(img) {
            // Setup canvas
            const canvas = document.getElementById('originalCanvas');
            const ctx = canvas.getContext('2d');
            
            // Resize jika terlalu besar (untuk performa)
            const maxWidth = 800;
            let width = img.width;
            let height = img.height;
            
            if (width > maxWidth) {
                height = (height * maxWidth) / width;
                width = maxWidth;
            }
            
            canvas.width = width;
            canvas.height = height;
            ctx.drawImage(img, 0, 0, width, height);

            // Convert to OpenCV Mat
            originalImage = cv.imread(canvas);

            // Detect document corners
            detectDocumentCorners(originalImage);

            // Show canvas and action buttons
            document.getElementById('canvasContainer').style.display = 'block';
            document.getElementById('actionButtons').style.display = 'flex';
            document.getElementById('previewContainer').style.display = 'none';

            showStatus('✅ Foto berhasil! Adjust corners jika perlu.', false);
        }

        function detectDocumentCorners(src) {
            try {
                // Convert to grayscale
                let gray = new cv.Mat();
                cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

                // Blur untuk reduce noise
                let blurred = new cv.Mat();
                cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);

                // Edge detection (Canny)
                let edges = new cv.Mat();
                cv.Canny(blurred, edges, 50, 150);

                // Find contours
                let contours = new cv.MatVector();
                let hierarchy = new cv.Mat();
                cv.findContours(edges, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

                // Find largest rectangular contour
                let maxArea = 0;
                let bestContour = null;

                for (let i = 0; i < contours.size(); i++) {
                    let contour = contours.get(i);
                    let area = cv.contourArea(contour);
                    
                    if (area > maxArea && area > src.rows * src.cols * 0.1) {
                        // Approximate contour to polygon
                        let peri = cv.arcLength(contour, true);
                        let approx = new cv.Mat();
                        cv.approxPolyDP(contour, approx, 0.02 * peri, true);
                        
                        // Check if it's a quadrilateral
                        if (approx.rows === 4) {
                            maxArea = area;
                            bestContour = approx;
                        }
                        
                        approx.delete();
                    }
                    contour.delete();
                }

                // Get corners or use default
                if (bestContour) {
                    detectedCorners = [];
                    for (let i = 0; i < 4; i++) {
                        detectedCorners.push({
                            x: bestContour.data32S[i * 2],
                            y: bestContour.data32S[i * 2 + 1]
                        });
                    }
                    bestContour.delete();
                } else {
                    // Default corners (full image)
                    detectedCorners = [
                        { x: 0, y: 0 },
                        { x: src.cols, y: 0 },
                        { x: src.cols, y: src.rows },
                        { x: 0, y: src.rows }
                    ];
                }

                // Order corners: top-left, top-right, bottom-right, bottom-left
                detectedCorners = orderCorners(detectedCorners);

                // Draw corners on UI
                drawCorners();

                // Cleanup
                gray.delete();
                blurred.delete();
                edges.delete();
                contours.delete();
                hierarchy.delete();

            } catch (error) {
                console.error('Error detecting corners:', error);
                // Fallback to default corners
                detectedCorners = [
                    { x: 0, y: 0 },
                    { x: src.cols, y: 0 },
                    { x: src.cols, y: src.rows },
                    { x: 0, y: src.rows }
                ];
                drawCorners();
            }
        }

        function orderCorners(corners) {
            // Sort by y first
            corners.sort((a, b) => a.y - b.y);
            
            // Top two points
            let topPoints = corners.slice(0, 2);
            topPoints.sort((a, b) => a.x - b.x);
            
            // Bottom two points
            let bottomPoints = corners.slice(2, 4);
            bottomPoints.sort((a, b) => a.x - b.x);
            
            return [topPoints[0], topPoints[1], bottomPoints[1], bottomPoints[0]];
        }

        function drawCorners() {
            const canvas = document.getElementById('originalCanvas');
            const cornersDiv = document.getElementById('corners');
            cornersDiv.innerHTML = '';

            canvasRect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / canvasRect.width;
            const scaleY = canvas.height / canvasRect.height;

            // Draw lines between corners on canvas
            const ctx = canvas.getContext('2d');
            const img = cv.imread(canvas);
            cv.imshow(canvas, originalImage);

            ctx.strokeStyle = '#2563eb';
            ctx.lineWidth = 3;
            ctx.beginPath();
            ctx.moveTo(detectedCorners[0].x, detectedCorners[0].y);
            for (let i = 1; i < detectedCorners.length; i++) {
                ctx.lineTo(detectedCorners[i].x, detectedCorners[i].y);
            }
            ctx.closePath();
            ctx.stroke();

            // Create draggable corner points
            detectedCorners.forEach((corner, index) => {
                const point = document.createElement('div');
                point.className = 'corner-point';
                point.style.left = (corner.x / scaleX) + 'px';
                point.style.top = (corner.y / scaleY) + 'px';
                point.dataset.index = index;

                // Touch events
                point.addEventListener('touchstart', startDrag);
                point.addEventListener('mousedown', startDrag);

                cornersDiv.appendChild(point);
            });

            img.delete();
        }

        function startDrag(e) {
            e.preventDefault();
            isDragging = true;
            draggedCornerIndex = parseInt(e.target.dataset.index);
            e.target.classList.add('active');

            document.addEventListener('touchmove', onDrag);
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('touchend', stopDrag);
            document.addEventListener('mouseup', stopDrag);
        }

        function onDrag(e) {
            if (!isDragging || draggedCornerIndex < 0) return;
            e.preventDefault();

            const canvas = document.getElementById('originalCanvas');
            canvasRect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / canvasRect.width;
            const scaleY = canvas.height / canvasRect.height;

            let clientX, clientY;
            if (e.type === 'touchmove') {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }

            const x = (clientX - canvasRect.left) * scaleX;
            const y = (clientY - canvasRect.top) * scaleY;

            // Update corner position
            detectedCorners[draggedCornerIndex] = { x, y };
            
            // Redraw
            drawCorners();
        }

        function stopDrag(e) {
            if (isDragging) {
                document.querySelectorAll('.corner-point').forEach(p => p.classList.remove('active'));
            }
            isDragging = false;
            draggedCornerIndex = -1;

            document.removeEventListener('touchmove', onDrag);
            document.removeEventListener('mousemove', onDrag);
            document.removeEventListener('touchend', stopDrag);
            document.removeEventListener('mouseup', stopDrag);
        }

        function processDocument() {
            showStatus('✨ Memproses dokumen...', false);

            try {
                // Destination points (A4 ratio)
                const width = 800;
                const height = 1132; // A4 ratio
                
                const dstCorners = [
                    { x: 0, y: 0 },
                    { x: width, y: 0 },
                    { x: width, y: height },
                    { x: 0, y: height }
                ];

                // Convert corners to OpenCV format
                const srcMat = cv.matFromArray(4, 1, cv.CV_32FC2, [
                    detectedCorners[0].x, detectedCorners[0].y,
                    detectedCorners[1].x, detectedCorners[1].y,
                    detectedCorners[2].x, detectedCorners[2].y,
                    detectedCorners[3].x, detectedCorners[3].y
                ]);

                const dstMat = cv.matFromArray(4, 1, cv.CV_32FC2, [
                    0, 0,
                    width, 0,
                    width, height,
                    0, height
                ]);

                // Get perspective transform matrix
                const M = cv.getPerspectiveTransform(srcMat, dstMat);

                // Apply perspective warp
                let warped = new cv.Mat();
                cv.warpPerspective(originalImage, warped, M, new cv.Size(width, height));

                // Enhancement: Convert to grayscale
                let gray = new cv.Mat();
                cv.cvtColor(warped, gray, cv.COLOR_RGBA2GRAY);

                // Apply adaptive threshold untuk hasil lebih jelas
                let enhanced = new cv.Mat();
                cv.adaptiveThreshold(gray, enhanced, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY, 11, 2);

                // Show result
                const canvas = document.getElementById('processedCanvas');
                canvas.width = width;
                canvas.height = height;
                cv.imshow(canvas, enhanced);

                // Show preview container
                document.getElementById('previewContainer').style.display = 'block';
                document.getElementById('actionButtons').style.display = 'none';

                showStatus('✅ Dokumen berhasil di-scan!', false);

                // Cleanup
                srcMat.delete();
                dstMat.delete();
                M.delete();
                warped.delete();
                gray.delete();
                enhanced.delete();

            } catch (error) {
                console.error('Error processing document:', error);
                showStatus('❌ Gagal memproses dokumen. Coba lagi.', true);
            }
        }

        function saveDocument() {
            showStatus('💾 Menyimpan dokumen...', false);
            
            const canvas = document.getElementById('processedCanvas');
            canvas.toBlob(async function(blob) {
                try {
                    // Simulasi upload (bisa diganti dengan upload ke server nanti)
                    const formData = new FormData();
                    formData.append('photo', blob, 'scanned_' + Date.now() + '.jpg');
                    formData.append('test_mode', '1'); // Flag untuk test
                    
                    // Untuk sekarang: download saja dulu
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'scanned_document_' + Date.now() + '.jpg';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);

                    showStatus('✅ Dokumen tersimpan! (Test mode - file downloaded)', false);
                    
                    // Auto reset setelah 2 detik
                    setTimeout(() => {
                        resetCamera();
                    }, 2000);

                } catch (error) {
                    console.error('Save error:', error);
                    showStatus('❌ Gagal menyimpan. Coba lagi.', true);
                }
            }, 'image/jpeg', 0.95);
        }

        function resetCamera() {
            document.getElementById('canvasContainer').style.display = 'none';
            document.getElementById('actionButtons').style.display = 'none';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('cameraInput').value = '';
            
            if (originalImage) {
                originalImage.delete();
                originalImage = null;
            }
            
            detectedCorners = [];
            showStatus('📸 Siap untuk foto baru!', false);
        }
    </script>
</body>
</html>
