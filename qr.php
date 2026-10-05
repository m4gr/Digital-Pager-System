<?php
declare(strict_types=1);

// Build absolute URL to customer.php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$defaultUrl = $scheme . '://' . '192.168.8.155' . $dir . '/customer.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Webix Queue - QR Code</title>
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
            <a href="index.php" class="nav-link">لوحة الموظف</a>
            <a href="qr.php" class="nav-link active">QR Code</a>
        </nav>
    </div>
</header>

<main class="container container-narrow">
    <section class="card">
        <h1 class="card-title">QR Code لصفحة العميل</h1>
        <p class="card-subtitle">
            اطبع هذا الـ QR وضعه على الطاولات. عند مسحه سيفتح العميل صفحة متابعة الطلب مباشرة.
        </p>

        <div class="field">
            <label for="qrUrl">رابط صفحة العميل</label>
            <input type="url" id="qrUrl" value="<?= htmlspecialchars($defaultUrl, ENT_QUOTES) ?>">
        </div>

        <div class="qr-preview">
            <div id="qrBox" class="qr-box">
                <div class="loading-spinner"></div>
            </div>
        </div>

        <p class="hint center-hint">
            ملاحظة: إذا فتحت الموقع من جهاز آخر على نفس الشبكة، استبدل <code>localhost</code>
            بـ IP جهاز الكمبيوتر (مثال: <code>192.168.1.10</code>) حتى يعمل المسح من الجوال.
        </p>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    var qrBox = document.getElementById('qrBox');
    var urlInput = document.getElementById('qrUrl');
    var qrInstance = null;

    function renderQr(text) {
        if (!text) return;
        qrBox.innerHTML = '';
        // qrcodejs renders a canvas + img
        qrInstance = new QRCode(qrBox, {
            text: text,
            width: 220,
            height: 220,
            colorDark: '#0f172a',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    urlInput.addEventListener('change', function () {
        renderQr(urlInput.value.trim());
    });
    urlInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            renderQr(urlInput.value.trim());
        }
    });

    renderQr(urlInput.value.trim());
})();
</script>
</body>
</html>