<?php
declare(strict_types=1);
require __DIR__ . '/../connect.php';

$rows = $pdo->query(
    "SELECT order_number, phone, status, created_at, called_at
     FROM orders
     ORDER BY (status = 'ready') DESC, created_at DESC
     LIMIT 50"
)->fetchAll();

$statusLabels = [
    'waiting'   => 'قيد الانتظار',
    'preparing' => 'قيد التحضير',
    'ready'     => 'جاهز',
    'completed' => 'تم الاستلام',
];

$orders = array_map(static function (array $r) use ($statusLabels): array {
    return [
        'order_number' => $r['order_number'],
        'status'       => $r['status'],
        'status_label' => $statusLabels[$r['status']] ?? 'غير معروف',
        'has_customer' => !empty($r['phone']),
        'created_at'   => $r['created_at'],
        'called_at'    => $r['called_at'],
    ];
}, $rows);

json_response(['ok' => true, 'orders' => $orders]);