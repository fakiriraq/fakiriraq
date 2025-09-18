<?php
// api/products.php
header('Content-Type: application/json; charset=utf-8');

// المسار النسبي إلى ملف البيانات
$productsFile = __DIR__ . '/../data/products.json';

if (!file_exists($productsFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Products file not found.']);
    exit;
}

$content = file_get_contents($productsFile);
if ($content === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to read products file.']);
    exit;
}

// تأمين JSON صالح
$data = json_decode($content, true);
if ($data === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid products JSON.']);
    exit;
}

echo json_encode($data);
