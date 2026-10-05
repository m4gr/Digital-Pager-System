/* Webix Queue - Customer status page (iPhone-safe audio) */
(function () {
    'use strict';

    /* ---------------- Elements ---------------- */
    var registerView   = document.getElementById('registerView');
    var statusView     = document.getElementById('statusView');
    var registerForm   = document.getElementById('registerForm');
    var registerBtn    = document.getElementById('registerBtn');
    var orderInput     = document.getElementById('orderNumber');
    var phoneInput     = document.getElementById('phone');

    var displayOrder   = document.getElementById('displayOrder');
    var statusTitle    = document.getElementById('statusTitle');
    var statusDesc     = document.getElementById('statusDesc');
    var statusVisual   = document.getElementById('statusVisual');
    var statusIcon     = document.getElementById('statusIcon');
    var changeOrderBtn = document.getElementById('changeOrderBtn');
    var testNotifyBtn  = document.getElementById('testNotifyBtn');

    var soundModal      = document.getElementById('soundModal');
    var activateSoundBtn = document.getElementById('activateSoundBtn');

    var readyModal     = document.getElementById('readyModal');
    var modalCloseBtn  = document.getElementById('modalCloseBtn');
    var modalSub       = document.getElementById('modalSub');

    var toastBox       = document.getElementById('toastBox');

    /* ---------------- State ---------------- */
    var currentOrder = null;
    var pollTimer = null;
    var notificationTriggered = false;
    var soundReady = false;

    /* ---------------- Single Audio element ---------------- */
    var notificationSound = new Audio('assets/notification.mp3');
    notificationSound.preload = 'auto';
    notificationSound.volume = 1.0;
    notificationSound.addEventListener('error', function () {
        console.warn('[Webix] notification.mp3 failed to load. Place the file at assets/notification.mp3');
    });

    /* ---------------- Toast ---------------- */
    function showToast(message, type) {
        type = type || 'info';
        var el = document.createElement('div');
        el.className = 'toast toast-' + type;
        el.textContent = message;
        toastBox.appendChild(el);
        setTimeout(function () {
            el.style.transition = 'opacity .25s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 250);
        }, 3200);
    }

    /* ---------------- Icons ---------------- */
    var ICON_WAIT =
        '<svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="currentColor" ' +
        'stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';

    var ICON_READY =
        '<svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="currentColor" ' +
        'stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M20 6L9 17l-5-5"/></svg>';

    var ICON_DONE =
        '<svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="currentColor" ' +
        'stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M3 7h18M5 7l1 13a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-13M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>';

    /* ---------------- API ---------------- */
    function postForm(url, data) {
        var body = new URLSearchParams(data).toString();
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    function fetchStatus(order) {
        return fetch('customer_status.php?order=' + encodeURIComponent(order),
                     { cache: 'no-store' })
            .then(function (r) { return r.json(); });
    }

    /* ---------------- Vibration ---------------- */
    function vibrate() {
        if ('vibrate' in navigator) {
            try { navigator.vibrate([300, 150, 300, 150, 500]); } catch (e) {}
        }
    }

    /* ---------------- Sound activation ---------------- */
    function isSoundEnabled() {
        try { return localStorage.getItem('soundEnabled') === 'true'; }
        catch (e) { return false; }
    }

    function markSoundEnabled() {
        try { localStorage.setItem('soundEnabled', 'true'); } catch (e) {}
    }

    /**
     * Must be called synchronously inside a user gesture (click).
     * Plays the sound briefly to unlock iOS audio for this page session.
     */
    function activateSound(onDone) {
        try {
            notificationSound.currentTime = 0;
            var p = notificationSound.play();
            if (p && typeof p.then === 'function') {
                p.then(function () {
                    // Played at least momentarily — audio unlocked.
                    soundReady = true;
                    markSoundEnabled();
                    if (typeof onDone === 'function') onDone(true);
                }).catch(function (err) {
                    // iOS may still block; keep graceful.
                    console.log('Audio activation blocked:', err);
                    if (typeof onDone === 'function') onDone(false);
                });
            } else {
                soundReady = true;
                markSoundEnabled();
                if (typeof onDone === 'function') onDone(true);
            }
        } catch (e) {
            console.log('Audio activation error:', e);
            if (typeof onDone === 'function') onDone(false);
        }
    }

    function playNotificationSound() {
        try {
            notificationSound.currentTime = 0;
            var p = notificationSound.play();
            if (p && typeof p.catch === 'function') {
                p.catch(function (err) {
                    // Silent fail — modal + vibration will still notify.
                    console.log('Audio playback was blocked:', err);
                });
            }
        } catch (e) {
            console.log('Audio playback error:', e);
        }
    }

    /* ---------------- Sound modal ---------------- */
    function showSoundModal() {
        soundModal.classList.remove('hidden');
    }
    function hideSoundModal() {
        soundModal.classList.add('hidden');
    }

    activateSoundBtn.addEventListener('click', function () {
        activateSound(function (ok) {
            hideSoundModal();
            if (ok) {
                showToast('تم تفعيل تنبيه الطلب', 'success');
            } else {
                // Graceful: still proceed, user can use vibration/modal.
                showToast('تعذر تفعيل الصوت، سيظهر تنبيه بصري عند جهوزية الطلب', 'info');
            }
            // Focus attention on test button
            if (testNotifyBtn) testNotifyBtn.focus();
        });
    });

    /* ---------------- Register ---------------- */
    registerForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var order = (orderInput.value || '').trim();
        var phone = (phoneInput.value || '').trim();

        if (!/^[A-Za-z0-9\-]{1,20}$/.test(order)) {
            showToast('رقم الطلب غير صالح', 'error');
            orderInput.focus();
            return;
        }
        if (!/^05[0-9]{8}$/.test(phone)) {
            showToast('رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام', 'error');
            phoneInput.focus();
            return;
        }

        registerBtn.disabled = true;
        registerBtn.textContent = 'جارٍ المتابعة...';

        postForm('api/create_order.php', { order_number: order, phone: phone })
            .then(function (res) {
                if (!res.ok) {
                    var msg = 'تعذر متابعة الطلب';
                    if (res.error === 'invalid_phone') msg = 'رقم الجوال غير صالح';
                    if (res.error === 'invalid_order_number') msg = 'رقم الطلب غير صالح';
                    showToast(msg, 'error');
                    return;
                }
                enterStatusView(res.order, res.status);
            })
            .catch(function () {
                showToast('خطأ في الاتصال بالخادم', 'error');
            })
            .finally(function () {
                registerBtn.disabled = false;
                registerBtn.textContent = 'متابعة الطلب';
            });
    });

    /* ---------------- Status view ---------------- */
    function enterStatusView(order, status) {
        currentOrder = order;
        notificationTriggered = false;

        registerView.classList.add('hidden');
        statusView.classList.remove('hidden');
        displayOrder.textContent = '#' + order;
        modalSub.textContent = 'طلبك #' + order;

        // Apply initial status
        applyStatus(status);

        // Show sound activation modal only if not enabled before
        if (!isSoundEnabled()) {
            showSoundModal();
        }

        // Start polling
        startPolling();
    }

    function applyStatus(status) {
        statusView.classList.remove('status-view-ready', 'status-view-completed');

        if (status === 'waiting') {
            statusIcon.innerHTML = ICON_WAIT;
            statusTitle.textContent = 'بانتظار الطلب';
            statusDesc.textContent = 'سنقوم بتنبيهك عندما يصبح طلبك جاهزًا.';
        } else if (status === 'preparing') {
            statusIcon.innerHTML = ICON_WAIT;
            statusTitle.textContent = 'جاري تحضير طلبك';
            statusDesc.textContent = 'يرجى الانتظار، سيتم تنبيهك عند الجهوزية.';
        } else if (status === 'ready') {
            statusView.classList.add('status-view-ready');
            statusIcon.innerHTML = ICON_READY;
            statusTitle.textContent = 'طلبك جاهز للاستلام';
            statusDesc.textContent = 'يرجى التوجه إلى الكاشير لاستلام طلبك.';

            if (!notificationTriggered) {
                notificationTriggered = true;
                triggerReadyAlert();
            }
        } else if (status === 'completed') {
            statusView.classList.add('status-view-completed');
            statusIcon.innerHTML = ICON_DONE;
            statusTitle.textContent = 'تم استلام الطلب';
            statusDesc.textContent = 'شكرًا لك، نتمنى لك يومًا سعيدًا.';
            // Stop polling once completed
            stopPolling();
        }
    }

    function triggerReadyAlert() {
        // 1) Sound (may silently fail on iOS if not activated)
        playNotificationSound();
        // 2) Vibration
        vibrate();
        // 3) Modal
        readyModal.classList.remove('hidden');
        // 4) Change page title temporarily
        var originalTitle = document.title;
        document.title = 'طلبك جاهز!';
        // Restore title when modal is closed
        var restore = function () {
            document.title = originalTitle;
            modalCloseBtn.removeEventListener('click', restore);
        };
        modalCloseBtn.addEventListener('click', restore);
    }

    /* ---------------- Polling ---------------- */
    function startPolling() {
        stopPolling();
        pollTimer = setInterval(pollOnce, 3000);
        pollOnce();
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function pollOnce() {
        if (!currentOrder) return;
        fetchStatus(currentOrder)
            .then(function (res) {
                if (!res.ok) return;
                applyStatus(res.status);
            })
            .catch(function () {});
    }

    /* ---------------- Ready modal close ---------------- */
    modalCloseBtn.addEventListener('click', function () {
        readyModal.classList.add('hidden');
    });
    readyModal.addEventListener('click', function (e) {
        if (e.target === readyModal) readyModal.classList.add('hidden');
    });

    /* ---------------- Test notification ---------------- */
    testNotifyBtn.addEventListener('click', function () {
        // If the user hasn't activated yet, activate first within this gesture
        var doTest = function () {
            playNotificationSound();
            vibrate();
            readyModal.classList.remove('hidden');
            // Optional: revert after few seconds if it was just a test
            setTimeout(function () {
                // Only auto-close if order isn't actually ready
                if (currentOrder && statusTitle.textContent !== 'طلبك جاهز للاستلام') {
                    readyModal.classList.add('hidden');
                }
            }, 2500);
        };

        if (!soundReady && !isSoundEnabled()) {
            activateSound(function () { doTest(); });
        } else {
            doTest();
        }
    });

    /* ---------------- Change order ---------------- */
    changeOrderBtn.addEventListener('click', function () {
        stopPolling();
        currentOrder = null;
        notificationTriggered = false;
        readyModal.classList.add('hidden');
        soundModal.classList.add('hidden');

        statusView.classList.add('hidden');
        registerView.classList.remove('hidden');
        phoneInput.value = '';
        orderInput.value = '';
        orderInput.focus();
    });

    /* ---------------- URL prefill ---------------- */
    var params = new URLSearchParams(window.location.search);
    var urlOrder = params.get('order');
    if (urlOrder && /^[A-Za-z0-9\-]{1,20}$/.test(urlOrder)) {
        orderInput.value = urlOrder;
    }

    /* ---------------- Visibility ---------------- */
    document.addEventListener('visibilitychange', function () {
        if (!currentOrder) return;
        if (document.hidden) {
            stopPolling();
        } else {
            startPolling();
            // If we returned and order is already ready but modal was closed,
            // re-show the ready state (no re-notification).
            if (notificationTriggered &&
                statusView.classList.contains('status-view-ready')) {
                // Do nothing — user already saw it.
            }
        }
    });

    /* ---------------- Restore soundReady from localStorage ---------------- */
    if (isSoundEnabled()) {
        // We trust the flag but still mark ready so test button works immediately.
        soundReady = true;
    }
})();