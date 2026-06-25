// ============================================================
//  CONTROLLER: AppController.js
//  Hooks up Models and Views
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Dashboard
    init();
});

function init() {
    // 1. Render the services grid
    const services = ServiceModel.getAll();
    ServiceView.render(services, handleServiceClick);

    // 2. Initial render of the order list (empty)
    updateOrderDisplay();

    // 3. Setup event listeners
    document.getElementById('btnClearAll').addEventListener('click', handleClearAll);
    document.getElementById('btnPlaceOrder').addEventListener('click', handlePlaceOrder);
    document.getElementById('btnPrintReceipt').addEventListener('click', handlePrintReceipt);
    document.getElementById('btnTransactionHistory').addEventListener('click', handleShowTransactionHistory);
    document.getElementById('btnLogout').addEventListener('click', handleLogout);

    // 4. Start Navbar Datetime ticker
    startDatetimeTicker();
}

// Update order lists and details panel
function updateOrderDisplay(lastTxn = null) {
    const items = OrderModel.getItems();
    const total = OrderModel.getTotal();
    const shopInfo = ServiceModel.getShopInfo();

    // Render left current order
    OrderView.renderOrder(items, total, handleRemoveItem, handleQtyChange);

    // Render receipt preview
    OrderView.renderReceiptPreview(items, total, shopInfo, lastTxn);
}

// Qty change handler
function handleQtyChange(itemId, newQty) {
    OrderModel.updateQty(itemId, newQty);
    
    // Smooth update of individual item row price, total amount, and receipt preview
    // to avoid rebuilding the list and losing keyboard focus while typing.
    const total = OrderModel.getTotal();
    const items = OrderModel.getItems();
    const item = items.find(i => i._id === itemId);
    
    const list = document.getElementById('orderItemsList');
    if (list && item) {
        const row = list.querySelector(`.qty-step-input[data-id="${itemId}"]`)?.closest('.order-item-card');
        if (row) {
            const priceEl = row.querySelector('.order-item-card-price');
            if (priceEl) {
                priceEl.textContent = '₱' + (item.unitPrice * item.qty).toFixed(2);
            }
        }
    }
    
    const totEl = document.getElementById('orderTotalDisplay');
    if (totEl) {
        totEl.textContent = 'P ' + total.toFixed(2);
    }
    
    const shopInfo = ServiceModel.getShopInfo();
    OrderView.renderReceiptPreview(items, total, shopInfo, null);
}

// When a service card is clicked, open customization modal
function handleServiceClick(serviceId) {
    const service = ServiceModel.getById(serviceId);
    if (!service) return;

    ModalView.open(service, (customizedItem) => {
        // customizedItem: { serviceId, serviceName, variantId, variantLabel, options, unitPrice, qty }
        OrderModel.addItem(customizedItem);
        updateOrderDisplay();
    });
}

// Remove item from order
function handleRemoveItem(itemId) {
    OrderModel.removeItem(itemId);
    updateOrderDisplay();
}

// Clear all items
function handleClearAll() {
    if (OrderModel.isEmpty()) return;
    
    Swal.fire({
        title: 'Clear order?',
        text: 'Are you sure you want to clear all items in the current order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2e4a6e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, clear it!'
    }).then((result) => {
        if (result.isConfirmed) {
            OrderModel.clearOrder();
            updateOrderDisplay();
        }
    });
}

// Place order - opens Payment Modal
function handlePlaceOrder() {
    if (OrderModel.isEmpty()) {
        Swal.fire({
            icon: 'warning',
            title: 'Empty Order',
            text: 'Please add items to the order first.',
            confirmButtonColor: '#2e4a6e'
        });
        return;
    }

    const total = OrderModel.getTotal();
    PaymentView.open(total, (paymentInfo) => {
        // Place the order in the model
        const txn = OrderModel.placeOrder(paymentInfo);

        // Show receipt in a modal
        showReceiptModal(txn);

        // Reset order view and show receipt details in order preview
        updateOrderDisplay(txn);
    });
}

// Show the receipt modal
function showReceiptModal(txn) {
    const titleEl = document.getElementById('receiptModalTitle');
    const subEl = document.getElementById('receiptModalSub');
    const bodyEl = document.getElementById('receiptModalBody');
    const shopInfo = ServiceModel.getShopInfo();

    titleEl.textContent = `Receipt – #${txn.orderNum}`;
    subEl.textContent = `Transaction #${txn.orderNum}`;

    const text = OrderView.buildReceiptText(
        txn.items,
        txn.total,
        shopInfo,
        txn.date,
        txn.time,
        '#' + txn.orderNum,
        txn
    );
    bodyEl.textContent = text;

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

// Print receipt action (just print preview or message)
function handlePrintReceipt() {
    const items = OrderModel.getItems();
    const total = OrderModel.getTotal();
    
    if (items.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Items',
            text: 'There is no current order to print.',
            confirmButtonColor: '#2e4a6e'
        });
        return;
    }
    
    // Trigger browser print of the receipt preview content
    const receiptContent = document.getElementById('receiptPreview').textContent;
    const printWindow = window.open('', '_blank', 'width=600,height=600');
    printWindow.document.write('<pre style="font-family:monospace;font-size:12px;padding:20px;">' + receiptContent + '</pre>');
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

// Show transaction history modal
function handleShowTransactionHistory() {
    const txns = OrderModel.getTransactions();
    const tbody = document.getElementById('txnTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    if (txns.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-3">No transactions yet.</td>
            </tr>
        `;
    } else {
        txns.forEach((t) => {
            const tr = document.createElement('tr');
            
            // Build items list short summary
            const itemsSummary = t.items.map(item => `${item.serviceName} (${item.qty}x)`).join(', ');
            
            tr.innerHTML = `
                <td>#${t.orderNum}</td>
                <td title="${itemsSummary}">${itemsSummary}</td>
                <td>${t.customer || 'Walk-in'}</td>
                <td>₱${t.total.toFixed(2)}</td>
                <td>${t.date} ${t.time}</td>
                <td>
                    <button class="btn btn-sm btn-primary btn-view-txn" data-num="${t.orderNum}" style="background-color:#2e4a6e;border-color:#2e4a6e;font-size:0.7rem;padding:2px 8px;">View</button>
                </td>
            `;
            
            tr.querySelector('.btn-view-txn').addEventListener('click', () => {
                const txn = OrderModel.getTransactionByNum(t.orderNum);
                if (txn) {
                    // Update preview and show modal
                    const shopInfo = ServiceModel.getShopInfo();
                    OrderView.renderReceiptPreview(txn.items, txn.total, shopInfo, txn);
                    showReceiptModal(txn);
                }
            });
            
            tbody.appendChild(tr);
        });
    }

    const modal = new bootstrap.Modal(document.getElementById('txnHistoryModal'));
    modal.show();
}

// Handle Logout
function handleLogout() {
    Swal.fire({
        title: 'Logout?',
        text: 'Are you sure you want to logout of PrintMaster POS?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2e4a6e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Logged Out',
                text: 'Redirecting to login page...',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'view/auth/login.php';
            });
        }
    });
}

// Datetime ticker in navbar
function startDatetimeTicker() {
    const el = document.getElementById('navDatetime');
    if (!el) return;

    function update() {
        const now = new Date();
        const optionsDate = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', optionsDate);
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        el.textContent = `${dateStr} | ${timeStr}`;
    }

    update();
    setInterval(update, 1000);
}
