// ============================================================
//  OrderView.php
//  JavaScript served as a PHP file.
//  When using a PHP server, add header('Content-Type: application/javascript') above.
// ============================================================
// ============================================================
//  VIEW: OrderView.js
// ============================================================

const OrderView = (() => {

    function renderOrder(items, total, onRemove, onQtyChange) {
        const list = document.getElementById('orderItemsList');
        const totEl = document.getElementById('orderTotalDisplay');
        if (!list) return;

        list.innerHTML = '';

        if (items.length === 0) {
            list.innerHTML = `
                <div class="order-empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    <p>No items yet</p>
                    <span>Click a service to add items</span>
                </div>`;
        } else {
            items.forEach(item => {
                const optionTags = (item.options || [])
                    .map(o => `<span class="order-item-tag">${o.optionLabel}</span>`)
                    .join('');

                const row = document.createElement('div');
                row.className = 'order-item-card';
                row.innerHTML = `
                    <div class="order-item-card-top">
                        <div class="order-item-card-info">
                            <div class="order-item-card-name">${item.serviceName}</div>
                            <div class="order-item-card-variant">${item.variantLabel}</div>
                            ${optionTags ? `<div class="order-item-tags">${optionTags}</div>` : ''}
                        </div>
                        <div class="order-item-card-right">
                            <div class="order-item-card-price">₱${(item.unitPrice * item.qty).toFixed(2)}</div>
                            <div class="order-item-card-each">₱${item.unitPrice.toFixed(2)} each</div>
                        </div>
                    </div>
                    <div class="order-item-card-bottom">
                        <div class="order-item-qty-stepper">
                            <button class="qty-step-btn qty-step-minus" data-id="${item._id}">−</button>
                            <input type="number" class="order-item-qty-input qty-step-input" min="1" value="${item.qty}" data-id="${item._id}" />
                            <button class="qty-step-btn qty-step-plus" data-id="${item._id}">+</button>
                        </div>
                        <button class="order-item-remove-btn" data-id="${item._id}">Remove</button>
                    </div>
                `;

                // Qty stepper minus
                row.querySelector('.qty-step-minus').addEventListener('click', () => {
                    const input = row.querySelector('.qty-step-input');
                    let val = parseInt(input.value) || 1;
                    if (val > 1) { val--; input.value = val; onQtyChange(item._id, val); }
                });
                // Qty stepper plus
                row.querySelector('.qty-step-plus').addEventListener('click', () => {
                    const input = row.querySelector('.qty-step-input');
                    let val = parseInt(input.value) || 1;
                    val++; input.value = val; onQtyChange(item._id, val);
                });
                // Keyboard input
                const qtyInput = row.querySelector('.qty-step-input');
                qtyInput.addEventListener('input', () => {
                    let val = parseInt(qtyInput.value);
                    if (!isNaN(val) && val >= 1) onQtyChange(item._id, val);
                });
                qtyInput.addEventListener('blur', () => {
                    let val = parseInt(qtyInput.value);
                    if (isNaN(val) || val < 1) { val = 1; qtyInput.value = 1; }
                    onQtyChange(item._id, val);
                });
                // Remove
                row.querySelector('.order-item-remove-btn').addEventListener('click', () => onRemove(item._id));

                list.appendChild(row);
            });
        }

        if (totEl) totEl.textContent = 'P ' + total.toFixed(2);
    }

    function renderReceiptPreview(items, total, shopInfo, txn) {
        const panel = document.getElementById('receiptPreview');
        if (!panel) return;

        // Clear receipt when order is empty and it's not showing a completed transaction
        if (items.length === 0 && !txn) {
            panel.textContent = '';
            return;
        }

        const now = new Date();
        const dateStr = txn ? txn.date : now.toISOString().slice(0, 10);
        const timeStr = txn ? txn.time : now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
        const orderNum = txn ? '#' + txn.orderNum : '#PENDING';
        panel.textContent = buildReceiptText(items, total, shopInfo, dateStr, timeStr, orderNum, txn);
    }

    function buildReceiptText(items, total, shopInfo, dateStr, timeStr, orderNum, txn) {
        const W = 40;
        const SEP = '-'.repeat(W);
        let r = '';
        r += center(shopInfo.name, W) + '\n';
        r += center(shopInfo.tagline, W) + '\n';
        r += center(shopInfo.address, W) + '\n';
        r += center(shopInfo.tel, W) + '\n';
        r += SEP + '\n';
        r += 'Date : ' + dateStr + '\n';
        r += 'Time : ' + timeStr + '\n';
        r += 'Order: ' + orderNum + '\n';

        if (txn && txn.customer) {
            r += SEP + '\n';
            r += 'Customer : ' + txn.customer + '\n';
            r += 'Contact  : ' + txn.contact + '\n';
        }

        r += SEP + '\n';
        r += padR('ITEM', 20) + padL('EACH', 6) + padL('QTY', 4) + padL('AMOUNT', 8) + '\n';
        r += SEP + '\n';

        items.forEach(item => {
            r += item.serviceName + ' (' + item.variantLabel + ')' + '\n';
            r += padL(item.unitPrice.toFixed(2), 30) +
                padL(String(item.qty), 4) +
                padL((item.unitPrice * item.qty).toFixed(2), 8) + '\n';
            (item.options || []).forEach(o => {
                r += '  - ' + o.groupLabel + ': ' + o.optionLabel + '\n';
            });
        });

        r += SEP + '\n';
        r += 'TOTAL' + padL(total.toFixed(2), W - 5) + '\n';
        r += SEP + '\n';

        if (txn) {
            r += 'PAYMENT METHOD' + padL(txn.paymentMethod, W - 14) + '\n';
            r += 'AMOUNT PAID' + padL(txn.amountPaid.toFixed(2), W - 11) + '\n';
            r += 'CHANGE' + padL(txn.change.toFixed(2), W - 6) + '\n';
            r += SEP + '\n';
        }

        r += center('Note:', W) + '\n';
        r += center('Please keep this receipt for', W) + '\n';
        r += center('transaction verification.', W) + '\n';
        r += SEP + '\n';
        r += center('Thank you for choosing', W) + '\n';
        r += center('Pili Print!', W) + '\n';
        r += center('Quality prints, every time.', W) + '\n';
        return r;
    }

    function center(str, w) {
        str = String(str);
        if (str.length >= w) return str;
        const p = Math.floor((w - str.length) / 2);
        return ' '.repeat(p) + str;
    }
    function padL(str, w) { str = String(str); return ' '.repeat(Math.max(0, w - str.length)) + str; }
    function padR(str, w) { str = String(str); return str + ' '.repeat(Math.max(0, w - str.length)); }

    return { renderOrder, renderReceiptPreview, buildReceiptText };
})();
