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
$phone       = trim((string)($input['phone'] ?? ''));

if (!preg_match('/^[A-Za-z0-9\-]{1,20}$/', $orderNumber)) {
    json_response(['ok' => false, 'error' => 'invalid_order_number'], 422);
}
if (!preg_match('/^05[0-9]{8}$/', $phone)) {
    json_response(['ok' => false, 'error' => 'invalid_phone'], 422);
}

// Try to find existing order
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE order_number = :n LIMIT 1");
$stmt->execute([':n' => $orderNumber]);
$existing = $stmt->fetch();

if ($existing) {
    // Link phone to existing order, keep status unless it was completed -> reset to waiting
    $newStatus = $existing['status'] === 'completed' ? 'waiting' : $existing['status'];
    $upd = $pdo->prepare(
        "UPDATE orders
         SET phone = :p, status = :s
         WHERE id = :id"
    );
    $upd->execute([
        ':p'  => $phone,
        ':s'  => $newStatus,
        ':id' => $existing['id'],
    ]);
    $status = $newStatus;
} else {
    // Create new order
    $ins = $pdo->prepare(
        "INSERT INTO orders (order_number, phone, status)
         VALUES (:n, :p, 'waiting')"
    );
    $ins->execute([':n' => $orderNumber, ':p' => $phone]);
    $status = 'waiting';
}

json_response([
    'ok'     => true,
    'order'  => $orderNumber,
    'status' => $status,
]);