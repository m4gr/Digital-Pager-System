<?php
declare(strict_types=1);
require __DIR__ . '/connect.php';

// Fetch current orders for initial render
$orders = $pdo->query(
    "SELECT order_number, phone, status, created_at, called_at
     FROM orders
     ORDER BY (status = 'ready') DESC, created_at DESC
     LIMIT 50"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Webix Queue - لوحة الموظف</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="app-header">
    <div class="container header-inner">
        <div class="brand">
            <span class="brand-mark">W</span>
            <span class="brand-name">Webix Queue</span>
        </div>
        <nav class="header-nav">
            <a href="index.php" class="nav-link active">لوحة الموظف</a>
            <a href="qr.php" class="nav-link">QR Code</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="card">
        <h1 class="card-title">مناداة طلب</h1>
        <p class="card-subtitle">أدخل رقم الطلب ثم اضغط "مناداة العميل" لتنبيهه.</p>

        <form id="callForm" class="call-form" autocomplete="off">
            <div class="field">
                <label for="orderNumber">رقم الطلب</label>
                <input type="text" id="orderNumber" name="order_number"
                       inputmode="numeric" pattern="[0-9]*"
                       placeholder="مثال: 128" required>
            </div>
            <button type="submit" class="btn btn-primary" id="callBtn">
                مناداة العميل
            </button>
        </form>
    </section>

    <section class="card">
        <div class="card-head">
            <h2 class="card-title">الطلبات الحالية</h2>
            <button class="btn btn-ghost" id="refreshBtn" type="button">تحديث</button>
        </div>

        <div id="ordersContainer" class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <p>لا توجد طلبات حتى الآن.</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                    <?php
                        $statusMap = [
                            'waiting'   => ['قيد الانتظار', 'badge-waiting'],
                            'preparing' => ['قيد التحضير', 'badge-preparing'],
                            'ready'     => ['جاهز', 'badge-ready'],
                            'completed' => ['تم الاستلام', 'badge-completed'],
                        ];
                        [$label, $cls] = $statusMap[$o['status']] ?? ['غير معروف', 'badge-waiting'];
                        $hasCustomer = !empty($o['phone']);
                    ?>
                    <div class="order-row" data-order="<?= e($o['order_number']) ?>">
                        <div class="order-main">
                            <span class="order-number">#<?= e($o['order_number']) ?></span>
                            <span class="badge <?= e($cls) ?>"><?= e($label) ?></span>
                        </div>
                        <div class="order-meta">
                            <span class="meta-item <?= $hasCustomer ? 'meta-on' : 'meta-off' ?>">
                                <?= $hasCustomer ? 'العميل مرتبط' : 'لا يوجد عميل' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<!-- Toast container -->
<div id="toastBox" class="toast-box" aria-live="polite"></div>

<script src="assets/js/dashboard.js"></script>
</body>
</html>