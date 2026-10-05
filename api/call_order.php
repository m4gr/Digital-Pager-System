<?php
declare(strict_types=1);
require __DIR__ . '/../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'method_not_allowed'], 405);
}

$input = $_POST;
if (empty($input) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
}

$orderNumber = trim((string)($input['order_number'] ?? ''));

if (!preg_match('/^[A-Za-z0-9\-]{1,20}$/', $orderNumber)) {
    json_response(['ok' => false, 'error' => 'invalid_order_number'], 422);
}

// Ensure order exists (create if not, without a phone)
$stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = :n LIMIT 1");
$stmt->execute([':n' => $orderNumber]);
$existing = $stmt->fetch();

if (!$existing) {
    $ins = $pdo->prepare(
        "INSERT INTO orders (order_number, phone, status)
         VALUES (:n, NULL, 'ready')"
    );
    $ins->execute([':n' => $orderNumber]);
} else {
    $upd = $pdo->prepare(
        "UPDATE orders
         SET status = 'ready', called_at = NOW()
         WHERE order_number = :n"
    );
    $upd->execute([':n' => $orderNumber]);
}

json_response([
    'ok'      => true,
    'order'   => $orderNumber,
    'status'  => 'ready',
    'message' => 'تمت مناداة الطلب #' . $orderNumber,
]);