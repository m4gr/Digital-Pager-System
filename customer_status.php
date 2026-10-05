<?php
declare(strict_types=1);
require __DIR__ . '/connect.php';

$orderNumber = trim((string)($_GET['order'] ?? ''));

if ($orderNumber === '' || !preg_match('/^[A-Za-z0-9\-]{1,20}$/', $orderNumber)) {
    json_response(['ok' => false, 'error' => 'invalid_order'], 400);
}

$stmt = $pdo->prepare(
    "SELECT order_number, status, called_at
     FROM orders
     WHERE order_number = :n
     LIMIT 1"
);
$stmt->execute([':n' => $orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    json_response(['ok' => false, 'error' => 'not_found'], 404);
}

json_response([
    'ok'     => true,
    'order'  => $order['order_number'],
    'status' => $order['status'],
    'called_at' => $order['called_at'],
]);