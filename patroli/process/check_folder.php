<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Folder Check</h2>";

$base_folder = __DIR__ . '/../qr-codes/';
echo "<p><strong>Base folder:</strong> " . $base_folder . "</p>";
echo "<p><strong>Exists:</strong> " . (is_dir($base_folder) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Writable:</strong> " . (is_writable($base_folder) ? 'YES' : 'NO') . "</p>";

$kode_kantor = '028';
$qr_folder = $base_folder . $kode_kantor;
echo "<hr>";
echo "<p><strong>KC 028 folder:</strong> " . $qr_folder . "</p>";
echo "<p><strong>Exists:</strong> " . (is_dir($qr_folder) ? 'YES' : 'NO') . "</p>";

if (!is_dir($qr_folder)) {
    echo "<p>Creating folder...</p>";
    if (mkdir($qr_folder, 0755, true)) {
        echo "<p style='color:green;'>✅ Folder created!</p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to create folder!</p>";
    }
}

echo "<hr>";
echo "<h3>Files in folder:</h3>";
if (is_dir($qr_folder)) {
    $files = scandir($qr_folder);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filepath = $qr_folder . '/' . $file;
            $size = filesize($filepath);
            echo "<li>$file ($size bytes)</li>";
        }
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Test Download QR Image:</h3>";

$test_qr_content = "028-R01";
$test_qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($test_qr_content);

echo "<p>URL: <a href='$test_qr_url' target='_blank'>$test_qr_url</a></p>";

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0'
    ]
]);

$qr_data = @file_get_contents($test_qr_url, false, $context);

if ($qr_data !== false) {
    echo "<p style='color:green;'>✅ QR download SUCCESS! (" . strlen($qr_data) . " bytes)</p>";
    
    $test_file = $qr_folder . '/TEST.png';
    if (file_put_contents($test_file, $qr_data)) {
        echo "<p style='color:green;'>✅ File saved: $test_file</p>";
        echo "<img src='../qr-codes/028/TEST.png' style='max-width:200px; border:2px solid green;'>";
    } else {
        echo "<p style='color:red;'>❌ Failed to save file!</p>";
    }
} else {
    echo "<p style='color:red;'>❌ QR download FAILED!</p>";
    echo "<p>Error: " . error_get_last()['message'] . "</p>";
}
