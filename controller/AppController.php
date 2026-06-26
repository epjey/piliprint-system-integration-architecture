<script>
    // ============================================================
    //  AppController.php
    //  JavaScript served as a PHP file.
    //  When using a PHP server, add header('Content-Type: application/javascript') above.
    //  This is the main entry point — hooks up Models and Views.
    // ============================================================
    // ============================================================
    //  CONTROLLER: AppController.js
    //  Hooks up Models and Views
    // ============================================================

    document.addEventListener('DOMContentLoaded', async () => {
        // Initialize Dashboard
        await init();
    });

    async function init() {
        // 1. Fetch services from database
        await ServiceModel.loadServices(false);
        // Also fetch initial transactions
        await OrderModel.loadTransactions();

        // 2. Render the services grid
        const services = ServiceModel.getAll();
        ServiceView.render(services, handleServiceClick);

        // 3. Initial render of the order list (empty)
        updateOrderDisplay();

        // 3. Setup event listeners
        document.getElementById('btnClearAll').addEventListener('click', handleClearAll);
        document.getElementById('btnPlaceOrder').addEventListener('click', handlePlaceOrder);
        document.getElementById('btnPrintReceipt').addEventListener('click', handlePrintReceipt);
        document.getElementById('btnTransactionHistory').addEventListener('click', handleShowTransactionHistory);
        document.getElementById('btnAdminLogin').addEventListener('click', () => { window.location.href = 'admin.php'; });
        document.getElementById('btnExportCSV').addEventListener('click', handleExportCSV);

        // Tap to Start overlay logic
        const overlay = document.getElementById('tapToStartOverlay');
        if (overlay) {
            overlay.addEventListener('click', () => {
                overlay.style.display = 'none';
            });
        }

        // 4. Start Navbar Datetime ticker
        startDatetimeTicker();

        // 5. Auto-sync services from database every 5 seconds (Ajax polling)
        setInterval(async () => {
            const oldServicesData = JSON.stringify(ServiceModel.getAll());
            await ServiceModel.loadServices(false);
            const newServicesData = JSON.stringify(ServiceModel.getAll());
            
            // Only re-render if the services data actually changed (to prevent UI flashing/interruptions)
            if (oldServicesData !== newServicesData) {
                const updatedServices = ServiceModel.getAll();
                ServiceView.render(updatedServices, handleServiceClick);
            }
        }, 5000);
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
        PaymentView.open(total, async (paymentInfo) => {
            // Place the order in the model
            const txn = await OrderModel.placeOrder(paymentInfo);

            if (txn) {
                // Show receipt in a modal
                showReceiptModal(txn);

                // Reset order view and show receipt details in order preview
                updateOrderDisplay(txn);
            } else {
                Swal.fire('Error', 'Failed to save transaction to database.', 'error');
            }
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
        const receiptPreview = document.getElementById('receiptPreview');
        const receiptContent = receiptPreview ? receiptPreview.textContent.trim() : "";

        if (!receiptContent) {
            Swal.fire({
                icon: 'info',
                title: 'No Receipt',
                text: 'There is no current order or transaction receipt to print.',
                confirmButtonColor: '#2e4a6e'
            });
            return;
        }

        // Trigger browser print of the receipt preview content
        const printWindow = window.open('', '_blank', 'width=600,height=600');
        printWindow.document.write('<pre style="font-family:monospace;font-size:12px;padding:20px;">' + receiptPreview.textContent + '</pre>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    // Export transaction history to CSV
    function handleExportCSV() {
        const txns = OrderModel.getTransactions();
        if (txns.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Transactions',
                text: 'There are no transactions available to export.',
                confirmButtonColor: '#2e4a6e'
            });
            return;
        }

        // CSV Headers
        let csv = "Order #,Items Summary,Customer,Total (PHP),Date & Time\n";

        txns.forEach(t => {
            const orderNum = `"${t.orderNum.replace(/"/g, '""')}"`;
            const itemsSummary = `"${t.items.map(item => `${item.serviceName} (${item.qty}x)`).join(', ').replace(/"/g, '""')}"`;
            const customer = `"${(t.customer || 'Walk-in').replace(/"/g, '""')}"`;
            const total = `"${t.total.toFixed(2)}"`;
            const dateTime = `"${t.date} ${t.time}"`;
            csv += `${orderNum},${itemsSummary},${customer},${total},${dateTime}\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `piliprint_transactions_${new Date().toISOString().slice(0, 10)}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Show transaction history modal
    async function handleShowTransactionHistory() {
        try {
            // Fetch fresh transactions before showing modal
            await OrderModel.loadTransactions();
            const txns = OrderModel.getTransactions();
            const tbody = document.getElementById('txnTableBody');
            if (!tbody) {
                console.error("tbody not found");
                return;
            }

            // Check if jQuery and DataTables are loaded
            if (typeof $ === 'undefined') {
                Swal.fire("Error", "jQuery failed to load! Please check the file path.", "error");
                return;
            }

            // Destroy existing DataTable if it has already been initialized
            if ($.fn.DataTable.isDataTable('#txnTable')) {
                $('#txnTable').DataTable().destroy();
            }

            tbody.innerHTML = '';

            txns.forEach((t) => {
                const tr = document.createElement('tr');

                // Build services summary
                const servicesSummary = Array.isArray(t.items)
                    ? t.items.map(i => `${i.serviceName || '?'} (${i.variantLabel || ''}) x${i.qty || 1}`).join(', ')
                    : '—';

                const statusBg = t.status === 'Pending' ? '#FEF9C3' : '#DCFCE7';
                const statusColor = t.status === 'Pending' ? '#CA8A04' : '#15803D';

                tr.innerHTML = `
                <td style="font-weight:700;white-space:nowrap;">#${t.orderNum}</td>
                <td style="white-space:nowrap;">${t.date || '—'}</td>
                <td style="white-space:nowrap;">${t.time || '—'}</td>
                <td>${t.customer || 'Walk-in'}</td>
                <td>${t.contact || '—'}</td>
                <td style="font-size:0.8rem;">${servicesSummary}</td>
                <td style="font-weight:600;color:#16A34A;white-space:nowrap;">₱${parseFloat(t.total || 0).toFixed(2)}</td>
                <td style="white-space:nowrap;">₱${parseFloat(t.amountPaid || 0).toFixed(2)}</td>
                <td style="white-space:nowrap;">₱${parseFloat(t.change || 0).toFixed(2)}</td>
                <td>${t.paymentMethod || '—'}</td>
                <td><span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:0.78rem;font-weight:600;background:${statusBg};color:${statusColor};">${t.status || 'Completed'}</span></td>
                <td>
                    <button class="btn btn-sm btn-primary btn-view-txn" data-num="${t.orderNum}" style="background-color:#0F172A;border-color:#0F172A;font-size:0.7rem;padding:4px 10px;">View Receipt</button>
                </td>
            `;

                tr.querySelector('.btn-view-txn').addEventListener('click', () => {
                    const txn = OrderModel.getTransactionByNum(t.orderNum);
                    if (txn) {
                        const shopInfo = ServiceModel.getShopInfo();
                        OrderView.renderReceiptPreview(txn.items, txn.total, shopInfo, txn);
                        showReceiptModal(txn);
                    }
                });

                tbody.appendChild(tr);
            });

            // Initialize DataTable
            $('#txnTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 5,
                "lengthMenu": [5, 10, 25],
                "scrollX": true,
                "language": {
                    "emptyTable": "No transactions yet."
                }
            });

            const modalEl = document.getElementById('txnHistoryModal');
            if (!modalEl) {
                Swal.fire("Error", "Modal HTML not found in index.html", "error");
                return;
            }

            let modal = bootstrap.Modal.getInstance(modalEl);
            if (!modal) {
                modal = new bootstrap.Modal(modalEl);
            }
            modal.show();
        } catch (e) {
            Swal.fire("Error", e.message, "error");
            console.error(e);
        }
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

</script>