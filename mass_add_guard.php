<?php
/**
 * MASS UPDATE: Add security guard to all agunan files
 * Run once: php mass_add_guard.php
 */

$files = [
    'ui/history.php',
    'ui/voucher_capture.php',
    'ui/voucher_history.php',
    'ui/agunan_detail.php',
    'ui/voucher_detail.php',
    'ui/voucher_list.php',
    'ui/voucher_capture_new.php',
    'ui/document_scanner_demo.php',
    'ui/check_location.php'
];

$old_code = "<?php\nsession_start();\nif (!isset(\$_SESSION['login'])) {\n    header('Location: ../index.php');\n    exit;\n}";

$new_code = "<?php\nsession_start();\n\n// CRITICAL SECURITY: Load agunan guard\nrequire_once '../agunan_guard.php';";

$updated = 0;
$errors = [];

foreach ($files as $file) {
    $filepath = __DIR__ . '/' . $file;
    
    if (!file_exists($filepath)) {
        $errors[] = "File not found: $file";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Try different variations
    $patterns = [
        "<?php\nsession_start();\nif (!isset(\$_SESSION['login'])) {\n    header('Location: ../index.php');\n    exit;\n}",
        "<?php\nsession_start();\nif (!isset(\$_SESSION['login'])) { header('Location: ../index.php'); exit; }",
        "<?php\nsession_start();\nif (!isset(\$_SESSION['login'])) {\n  header('Location: ../index.php');\n  exit;\n}"
    ];
    
    $replaced = false;
    foreach ($patterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            $content = str_replace($pattern, $new_code, $content);
            file_put_contents($filepath, $content);
            $updated++;
            $replaced = true;
            echo "✅ Updated: $file\n";
            break;
        }
    }
    
    if (!$replaced) {
        $errors[] = "Pattern not found in: $file";
    }
}

echo "\n✅ Updated: $updated files\n";
if (!empty($errors)) {
    echo "\n⚠️ Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}
