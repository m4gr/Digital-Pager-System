<?php
declare(strict_types=1);
require __DIR__ . '/connect.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Webix Queue - متابعة الطلب</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="customer-body">
<header class="app-header">
    <div class="container header-inner">
        <div class="brand">
            <span class="brand-mark">W</span>
            <span class="brand-name">Webix Queue</span>
        </div>
    </div>
</header>

<main class="container container-narrow">

    <!-- Step 1: Registration form -->
    <section id="registerView" class="card">
        <h1 class="card-title">متابعة الطلب</h1>
        <p class="card-subtitle">أدخل رقم طلبك ورقم جوالك لمتابعة حالة الطلب.</p>

        <form id="registerForm" class="stack-form" autocomplete="off" novalidate>
            <div class="field">
                <label for="orderNumber">رقم الطلب</label>
                <input type="text" id="orderNumber" name="order_number"
                       inputmode="numeric" pattern="[0-9]*"
                       placeholder="128" required>
            </div>
            <div class="field">
                <label for="phone">رقم الجوال</label>
                <input type="tel" id="phone" name="phone"
                       inputmode="tel" pattern="05[0-9]{8}"
                       placeholder="05XXXXXXXX" required>
                <span class="hint">مثال: 0551234567</span>
            </div>
            <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
                متابعة الطلب
            </button>
        </form>
    </section>

    <!-- Step 2: Status view -->
    <section id="statusView" class="card hidden">
        <div class="status-head">
            <span class="status-label">طلبك</span>
            <span class="status-order" id="displayOrder">#---</span>
        </div>

        <div class="status-visual" id="statusVisual">
            <div class="pulse-ring"></div>
            <div class="status-icon" id="statusIcon">
                <svg viewBox="0 0 24 24" width="42" height="42" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 2"/>
                </svg>
            </div>
        </div>

        <h2 class="status-title" id="statusTitle">بانتظار بدء التحضير</h2>
        <p class="status-desc" id="statusDesc">سنقوم بتنبيهك عندما يصبح طلبك جاهزًا.</p>

        <div class="status-meta">
            <span class="dot-live"></span>
            <span>يتم تحديث الحالة تلقائيًا</span>
        </div>

        <!-- Test notification button (small, always visible after activation) -->
        <button type="button" class="btn btn-ghost btn-block test-btn" id="testNotifyBtn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                 stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
            اختبار التنبيه
        </button>

        <button type="button" class="btn btn-ghost btn-block" id="changeOrderBtn">
            تغيير رقم الطلب
        </button>
    </section>

</main>

<!-- ================= Sound activation modal ================= -->
<div class="modal-overlay hidden" id="soundModal" role="dialog" aria-modal="true"
     aria-labelledby="soundModalTitle">
    <div class="modal">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" width="52" height="52" fill="none"
                 stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
            </svg>
        </div>
        <h2 id="soundModalTitle" class="modal-title">تفعيل تنبيه الطلب</h2>
        <p class="modal-desc">
            حتى نتمكن من تنبيهك عند جاهزية طلبك، فعّل التنبيه على جهازك.
        </p>
        <button type="button" class="btn btn-primary btn-block" id="activateSoundBtn">
            تفعيل التنبيه
        </button>
    </div>
</div>

<!-- ================= Ready modal ================= -->
<div class="modal-overlay hidden" id="readyModal" role="dialog" aria-modal="true"
     aria-labelledby="modalTitle">
    <div class="modal ready-modal">
        <div class="ready-anim">
            <svg viewBox="0 0 24 24" width="56" height="56" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <h2 id="modalTitle" class="modal-title">طلبك جاهز للاستلام</h2>
        <p class="modal-sub" id="modalSub">طلبك #---</p>
        <p class="modal-desc">يرجى التوجه إلى الكاشير لاستلام طلبك.</p>
        <button type="button" class="btn btn-primary btn-block" id="modalCloseBtn">
            حسنًا، شكرًا
        </button>
    </div>
</div>

<!-- Toast container -->
<div id="toastBox" class="toast-box" aria-live="polite"></div>

<script src="assets/js/customer.js"></script>
</body>
</html>