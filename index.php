<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PrintMaster POS</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="assets/DataTables/datatables.min.css" />
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="assets/sweetalert2/sweetalert2.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="pos-navbar">
        <!-- Left: logo + brand -->
        <div style="display:flex;align-items:center;gap:35px;margin-left:10px;">
            <div class="brand-icon-box">
                <img src="assets/logo/logo.png" alt="PiliPrint Logo"
                    style="height:100px;width:100px;object-fit:contain;border-radius:4px;transform:scale(2.2) translateY(8px);" />
            </div>
            <div>
                <div class="brand-name">PiliPrint</div>
                <div class="brand-sub">Printing Services</div>
            </div>
        </div>
        <!-- Right: datetime + buttons -->
        <div style="display:flex;align-items:center;gap:8px;">
            <div class="navbar-datetime" id="navDatetime"></div>
            <button class="btn btn-txn-history" id="btnTransactionHistory">Transaction History</button>
            <button class="btn btn-logout" id="btnLogout">Logout</button>
        </div>
    </nav>

    <!-- ===== MAIN LAYOUT ===== -->
    <div class="pos-layout">

        <!-- LEFT: Printing Services -->
        <div class="pos-services-panel">
            <div class="panel-header">Printing Services</div>
            <div class="services-grid" id="servicesGrid">
                <!-- Rendered by ServiceView.js -->
            </div>
        </div>

        <!-- CENTER: Current Order -->
        <div class="pos-order-panel">
            <div class="order-panel-header">
                <span class="order-panel-title">Current Order</span>
                <button class="btn-clear-all" id="btnClearAll">CLEAR ALL</button>
            </div>
            <div class="order-items-list" id="orderItemsList"></div>
            <div class="order-total-bar">
                <span class="total-label">Total</span>
                <span class="total-amount" id="orderTotalDisplay">P 0.00</span>
            </div>
            <div class="order-action-bar">
                <button class="btn-place-order" id="btnPlaceOrder">PLACE ORDER</button>
            </div>
        </div>

        <!-- RIGHT: Order Details -->
        <div class="pos-receipt-panel">
            <div class="panel-header">Order Details</div>
            <pre class="receipt-preview" id="receiptPreview"></pre>
            <div class="receipt-action-bar">
                <button class="btn-print-receipt" id="btnPrintReceipt">PRINT RECEIPT</button>
            </div>
        </div>

    </div>

    <!-- ===== ADD ITEM MODAL ===== -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
            <div class="modal-content pos-modal">
                <div class="pos-modal-header">
                    <div style="display:flex;align-items:center;">
                        <svg class="pos-modal-icon" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                        </svg>
                        <h5 class="modal-title">Add Item to Order</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Step Indicator -->
                <div class="modal-step-bar">
                    <div class="step-node">
                        <span class="step-circle active" id="stepCircle1">1</span>
                        <span class="step-label">Variant</span>
                    </div>
                    <div class="step-line" id="stepLine1"></div>
                    <div class="step-node">
                        <span class="step-circle" id="stepCircle2">2</span>
                        <span class="step-label">Options</span>
                    </div>
                    <div class="step-line" id="stepLine2"></div>
                    <div class="step-node">
                        <span class="step-circle" id="stepCircle3">3</span>
                        <span class="step-label">Qty</span>
                    </div>
                </div>

                <div class="pos-modal-body" id="modalBody"></div>

                <div class="modal-footer-bar">
                    <span class="item-total-label">Item Total</span>
                    <span class="item-total-amount" id="modalItemTotal">₱0.00</span>
                </div>
                <div class="modal-action-bar">
                    <button class="btn-modal-back" id="modalBackBtn">Cancel</button>
                    <button class="btn-modal-next" id="modalNextBtn">Next Step →</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== PAYMENT MODAL ===== -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content pos-modal">
                <div class="pos-modal-header">
                    <div style="display:flex;align-items:center;">
                        <svg class="pos-modal-icon" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect x="3" y="3" width="7" height="7" />
                            <rect x="14" y="3" width="7" height="7" />
                            <rect x="3" y="14" width="7" height="7" />
                            <rect x="14" y="14" width="7" height="7" />
                        </svg>
                        <h5 class="modal-title">Payment Calculator</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="padding:16px 20px 8px;">
                    <!-- Lookup row -->
                    <div class="row mb-2 align-items-center g-2">
                        <label class="col-auto payment-label" style="width:130px;">Lookup Returnee:</label>
                        <div class="col d-flex gap-2">
                            <input type="text" class="form-control form-control-sm" id="lookupInput" />
                            <button class="btn-lookup" id="btnLookup">Lookup</button>
                        </div>
                    </div>
                    <div class="row mb-2 align-items-center g-2">
                        <label class="col-auto payment-label" style="width:130px;">Customer Name:</label>
                        <div class="col"><input type="text" class="form-control form-control-sm" id="customerName" />
                        </div>
                    </div>
                    <div class="row mb-2 align-items-center g-2">
                        <label class="col-auto payment-label" style="width:130px;">Contact No:</label>
                        <div class="col"><input type="text" class="form-control form-control-sm" id="contactNo" /></div>
                    </div>

                    <div class="payment-total-text">Total Amount: <span id="paymentTotalAmt">P 0.00</span></div>

                    <div class="row mb-2 align-items-center g-2">
                        <label class="col-auto payment-label" style="width:130px;">Payment Method:</label>
                        <div class="col">
                            <select class="form-select form-select-sm" id="paymentMethod">
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>
                    </div>

                    <div class="payment-change-text" id="paymentChangeDisplay">Change: P 0.00</div>

                    <!-- GCash QR Code Area -->
                    <div id="gcashQrContainer"
                        style="display:none; text-align:center; padding:15px; background:#f8fafc; border-radius:8px; border:1px dashed #06B6D4; margin-top:10px; margin-bottom:10px;">
                        <div style="font-weight:700; color:#06B6D4; margin-bottom:8px; font-size:0.9rem;">Scan GCash QR
                            to Pay</div>
                        <img src="assets/logo/QR_Code_Example.svg.png" alt="GCash QR Code"
                            style="width:180px; height:180px; object-fit:contain; border:1px solid #ddd; padding:5px; background:#fff; border-radius:4px;" />
                        <div style="font-size:0.75rem; color:#64748B; margin-top:8px;">Amount to pay: <span
                                id="gcashQrAmount" style="font-weight:700; color:#0F172A;">P 0.00</span></div>
                    </div>

                    <!-- Numpad -->
                    <div id="numpadContainer">
                        <input type="text" class="numpad-display" id="numpadDisplay" placeholder="0" />
                        <div class="numpad-grid">
                            <button class="numpad-btn" data-val="7">7</button>
                            <button class="numpad-btn" data-val="8">8</button>
                            <button class="numpad-btn" data-val="9">9</button>
                            <button class="numpad-btn" data-val="4">4</button>
                            <button class="numpad-btn" data-val="5">5</button>
                            <button class="numpad-btn" data-val="6">6</button>
                            <button class="numpad-btn" data-val="1">1</button>
                            <button class="numpad-btn" data-val="2">2</button>
                            <button class="numpad-btn" data-val="3">3</button>
                            <button class="numpad-btn numpad-btn-clear" data-val="C">C</button>
                            <button class="numpad-btn" data-val="0">0</button>
                            <button class="numpad-btn numpad-btn-bksp" data-val="Bksp">Bksp</button>
                        </div>
                    </div>
                </div>

                <div class="payment-confirm-bar">
                    <button class="btn-confirm-payment" id="btnConfirmPayment">CONFIRM PAYMENT</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RECEIPT MODAL ===== -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content pos-modal">
                <div class="pos-modal-header">
                    <h5 class="modal-title" id="receiptModalTitle">Receipt – #0000</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="receipt-modal-sub" id="receiptModalSub">Transaction #0000</div>
                <pre class="receipt-modal-body" id="receiptModalBody"></pre>
                <button class="btn-receipt-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

    <!-- ===== TRANSACTION HISTORY MODAL ===== -->
    <div class="modal fade" id="txnHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content pos-modal">
                <div class="pos-modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title">Transaction History</h5>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <button class="btn btn-sm btn-success" id="btnExportCSV"
                            style="background-color: #10B981; border-color: #10B981; font-size: 0.8rem; padding: 5px 12px; font-weight: 600;">Export
                            CSV</button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" style="margin: 0;"></button>
                    </div>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <table id="txnTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Items Summary</th>
                                <th>Customer</th>
                                <th>Total (₱)</th>
                                <th>Date & Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="txnTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <!-- jQuery -->
    <script src="assets/jquery.js/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="assets/DataTables/datatables.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="assets/sweetalert2/sweetalert2.all.min.js"></script>
<<<<<<< HEAD
    <!-- MVC JS -->
    <script src="model/Ordermodel.js"></script>
    <script src="model/Servicemodel.js"></script>
    <script src="view/Serviceview.js"></script>
    <script src="view/Orderview.js"></script>
    <script src="view/Modalview.js"></script>
    <script src="view/Paymentview.js"></script>
    <script src="controller/AppController.js"></script>
=======
    <!-- MVC JS (PHP-served) -->
    <script src="model/ServiceModel.php"></script>
    <script src="model/OrderModel.php"></script>
    <script src="view/ServiceView.php"></script>
    <script src="view/OrderView.php"></script>
    <script src="view/ModalView.php"></script>
    <script src="view/PaymentView.php"></script>
    <script src="controller/AppController.php"></script>
>>>>>>> 3898005b24bfba47551b987153f4d8507418e35d
</body>

</html>