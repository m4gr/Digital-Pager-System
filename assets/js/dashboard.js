/* Webix Queue - Employee dashboard */
(function () {
    'use strict';

    var callForm = document.getElementById('callForm');
    var orderInput = document.getElementById('orderNumber');
    var callBtn = document.getElementById('callBtn');
    var ordersContainer = document.getElementById('ordersContainer');
    var refreshBtn = document.getElementById('refreshBtn');
    var toastBox = document.getElementById('toastBox');

    /* ------------- Toast ------------- */
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

    /* ------------- Fetch helpers ------------- */
    function postForm(url, data) {
        var body = new URLSearchParams(data).toString();
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
            body: body
        }).then(function (r) { return r.json(); });
    }

    function fetchOrders() {
        return fetch('api/get_orders.php', { cache: 'no-store' })
            .then(function (r) { return r.json(); });
    }

    /* ------------- Render ------------- */
    var STATUS_CLASS = {
        waiting:   'badge-waiting',
        preparing: 'badge-preparing',
        ready:     'badge-ready',
        completed: 'badge-completed'
    };

    function renderOrders(orders) {
        if (!orders || !orders.length) {
            ordersContainer.innerHTML =
                '<div class="empty-state"><p>لا توجد طلبات حتى الآن.</p></div>';
            return;
        }
        var html = orders.map(function (o) {
            var cls = STATUS_CLASS[o.status] || 'badge-waiting';
            var customer = o.has_customer ? 'العميل مرتبط' : 'لا يوجد عميل';
            var customerCls = o.has_customer ? 'meta-on' : 'meta-off';
            return '' +
                '<div class="order-row">' +
                    '<div class="order-main">' +
                        '<span class="order-number">#' + escapeHtml(o.order_number) + '</span>' +
                        '<span class="badge ' + cls + '">' + escapeHtml(o.status_label) + '</span>' +
                    '</div>' +
                    '<div class="order-meta">' +
                        '<span class="meta-item ' + customerCls + '">' + customer + '</span>' +
                    '</div>' +
                '</div>';
        }).join('');
        ordersContainer.innerHTML = html;
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function loadOrders(silent) {
        fetchOrders()
            .then(function (res) {
                if (!res.ok) throw new Error('failed');
                renderOrders(res.orders);
            })
            .catch(function () {
                if (!silent) showToast('تعذر تحميل الطلبات', 'error');
            });
    }

    /* ------------- Call order ------------- */
    callForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var value = (orderInput.value || '').trim();
        if (!value) {
            showToast('أدخل رقم الطلب', 'error');
            orderInput.focus();
            return;
        }
        if (!/^[A-Za-z0-9\-]{1,20}$/.test(value)) {
            showToast('رقم الطلب غير صالح', 'error');
            return;
        }

        callBtn.disabled = true;
        callBtn.textContent = 'جارٍ المناداة...';

        postForm('api/call_order.php', { order_number: value })
            .then(function (res) {
                if (!res.ok) {
                    showToast('تعذر تنفيذ العملية', 'error');
                    return;
                }
                showToast(res.message || ('تمت مناداة الطلب #' + value), 'success');
                orderInput.value = '';
                orderInput.focus();
                loadOrders(true);
            })
            .catch(function () {
                showToast('خطأ في الاتصال بالخادم', 'error');
            })
            .finally(function () {
                callBtn.disabled = false;
                callBtn.textContent = 'مناداة العميل';
            });
    });

    refreshBtn.addEventListener('click', function () { loadOrders(false); });

    /* ------------- Auto refresh ------------- */
    loadOrders(true);
    setInterval(function () { loadOrders(true); }, 5000);
})();