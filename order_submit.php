<?php
// api/order_submit.php
header('Content-Type: application/json; charset=utf-8');

// مسار ملف الحفظ
$ordersFile = __DIR__ . '/../data/orders.json';

// قراءة الـ body (JSON أو form)
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// إذا لم يكن JSON، حاول أخذ من $_POST
if ($input === null) {
    $input = $_POST;
}

// التحقق من الحقول الأساسية
$customerName = trim($input['customerName'] ?? '');
$customerAddress = trim($input['customerAddress'] ?? '');
$customerPhone = trim($input['customerPhone'] ?? '');
$cart = $input['cart'] ?? null;

$errors = [];
if ($customerName === '') $errors[] = 'الاسم مطلوب';
if ($customerAddress === '') $errors[] = 'العنوان مطلوب';
if (!is_array($cart) || count($cart) === 0) $errors[] = 'السلة فارغة أو غير صحيحة';

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// حساب المجموع
$total = 0.0;
foreach ($cart as $item) {
    $q = floatval($item['quantity'] ?? 0);
    $p = floatval($item['price'] ?? 0);
    $total += $q * $p;
}

// بناء كائن الطلب
$order = [
    'id' => uniqid('order_'),
    'timestamp' => date('c'),
    'customer' => [
        'name' => $customerName,
        'address' => $customerAddress,
        'phone' => $customerPhone
    ],
    'cart' => $cart,
    'total' => $total,
    'status' => 'new'
];

// اقرأ الطلبات السابقة وأضف الطلب الجديد
$orders = [];
if (file_exists($ordersFile)) {
    $prev = file_get_contents($ordersFile);
    $orders = json_decode($prev, true);
    if (!is_array($orders)) $orders = [];
}

// أضف الطلب ثم احفظ
$orders[] = $order;
if (file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'فشل حفظ الطلب']);
    exit;
}

// (اختياري) هنا يمكنك إرسال إشعار بالبريد الإلكتروني أو ربط API خارجي
// مثال: mail(...)

// رد للفرونت إند
echo json_encode(['success' => true, 'orderId' => $order['id']]);
