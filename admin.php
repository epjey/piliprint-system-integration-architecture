<?php
session_start();
if (!isset($_SESSION['admin_user_id'])) {
    header("Location: view/auth/login.php");
    exit;
}
if ($_SESSION['admin_role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PiliPrint - Admin</title>
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

    <!-- ===== ADMIN LAYOUT (full height, no top navbar) ===== -->
    <div class="admin-layout">

        <!-- LEFT: Sidebar -->
        <div class="admin-sidebar">

            <!-- Brand at top -->
            <div class="admin-sidebar-brand">
                <img src="assets/logo/logo.png" alt="PiliPrint Logo" class="admin-sidebar-logo" />
                <span class="admin-sidebar-brand-name">PiliPrint</span>
            </div>

            <!-- Navigation -->
            <div class="panel-header">Navigation</div>
            <ul class="admin-nav-list" id="adminNavList">
                <li class="admin-nav-item active" data-target="dashboard">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </li>
                <li class="admin-nav-item" data-target="services">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    Services
                </li>
                <li class="admin-nav-item" data-target="transactions">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Transactions
                </li>
                <li class="admin-nav-item" data-target="reports">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Reports
                </li>
                <li class="admin-nav-item" data-target="activity-log">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="8" y1="13" x2="16" y2="13"></line>
                        <line x1="8" y1="17" x2="13" y2="17"></line>
                        <circle cx="17" cy="17" r="3"></circle>
                        <line x1="19.5" y1="19.5" x2="21" y2="21"></line>
                    </svg>
                    Activity Log
                </li>
                <li class="admin-nav-item" data-target="manage-users">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Manage Users
                </li>
            </ul>

            <!-- Logout pinned at bottom -->
            <div class="admin-sidebar-footer">
                <button class="admin-sidebar-logout" id="btnLogout">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </button>
            </div>

        </div>

        <!-- RIGHT: Main Content -->
        <div class="admin-main-content">
            <div class="admin-content-header" id="adminContentHeader">
                Dashboard
            </div>
            <div class="admin-content-body" id="adminContentBody">
                <!-- Content injected here -->
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

    <!-- Chart.js -->
    <script src="assets/chart.js/chart.umd.min.js"></script>

    <!-- ===== MANAGE USERS MODALS ===== -->

    <!-- Add Cashier Modal -->
    <div class="modal fade" id="addCashierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:#0F172A;color:#fff;border-radius:12px 12px 0 0;padding:18px 24px;border-bottom:none;">
                    <h5 class="modal-title" style="font-weight:700;font-size:1rem;display:flex;align-items:center;gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        Add Cashier
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Email Address <span style="color:#EF4444;">*</span></label>
                        <input type="email" id="addEmail" class="form-control" placeholder="cashier@piliprint.com" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Password <span style="color:#EF4444;">*</span></label>
                        <input type="password" id="addPassword" class="form-control" placeholder="Enter password" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Confirm Password <span style="color:#EF4444;">*</span></label>
                        <input type="password" id="addConfirmPassword" class="form-control" placeholder="Repeat password" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                    <div class="mb-1">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Status</label>
                        <select id="addStatus" class="form-select" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:8px;padding:10px 14px;margin-top:14px;font-size:0.8rem;color:#15803D;">
                        <strong>Note:</strong> New accounts are always created with the <strong>Cashier</strong> role.
                    </div>
                </div>
                <div class="modal-footer" style="padding:16px 24px;border-top:1px solid #F1F5F9;gap:8px;">
                    <button class="btn" data-bs-dismiss="modal" style="background:#F1F5F9;color:#374151;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Cancel</button>
                    <button class="btn" id="btnSaveAddCashier" style="background:#F97316;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Cashier Modal -->
    <div class="modal fade" id="editCashierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
            <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:#0F172A;color:#fff;border-radius:12px 12px 0 0;padding:18px 24px;border-bottom:none;">
                    <h5 class="modal-title" style="font-weight:700;font-size:1rem;display:flex;align-items:center;gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Edit Cashier
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <input type="hidden" id="editUserId" />
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Role</label>
                        <input type="text" class="form-control" value="Cashier" disabled style="border-radius:8px;background:#F8FAFC;color:#64748B;border:1px solid #E2E8F0;padding:10px 14px;" />
                    </div>
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Email Address <span style="color:#EF4444;">*</span></label>
                        <input type="email" id="editEmail" class="form-control" placeholder="cashier@piliprint.com" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                    <div class="mb-1">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Status</label>
                        <select id="editStatus" class="form-select" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="padding:16px 24px;border-top:1px solid #F1F5F9;gap:8px;">
                    <button class="btn" data-bs-dismiss="modal" style="background:#F1F5F9;color:#374151;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Cancel</button>
                    <button class="btn" id="btnSaveEditCashier" style="background:#1D4ED8;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
            <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background:#D97706;color:#fff;border-radius:12px 12px 0 0;padding:18px 24px;border-bottom:none;">
                    <h5 class="modal-title" style="font-weight:700;font-size:1rem;display:flex;align-items:center;gap:10px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Reset Cashier Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <input type="hidden" id="resetUserId" />
                    <p style="font-size:0.85rem;color:#64748B;margin-bottom:16px;">You are resetting the password for: <strong id="resetUserEmail" style="color:#0F172A;"></strong></p>
                    <div class="mb-3">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">New Password <span style="color:#EF4444;">*</span></label>
                        <input type="password" id="resetNewPassword" class="form-control" placeholder="Enter new password" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                    <div class="mb-1">
                        <label style="font-size:0.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:block;">Confirm Password <span style="color:#EF4444;">*</span></label>
                        <input type="password" id="resetConfirmPassword" class="form-control" placeholder="Repeat new password" style="border-radius:8px;border:1px solid #D1D5DB;padding:10px 14px;font-size:0.9rem;" />
                    </div>
                </div>
                <div class="modal-footer" style="padding:16px 24px;border-top:1px solid #F1F5F9;gap:8px;">
                    <button class="btn" data-bs-dismiss="modal" style="background:#F1F5F9;color:#374151;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Cancel</button>
                    <button class="btn" id="btnSaveResetPassword" style="background:#D97706;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-weight:600;font-size:0.88rem;">Reset Password</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== ADMIN RECEIPT MODAL ===== -->
    <div class="modal fade" id="adminReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content pos-modal">
                <div class="pos-modal-header">
                    <h5 class="modal-title" id="adminReceiptModalTitle">Receipt – #0000</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="receipt-modal-sub" id="adminReceiptModalSub">Transaction #0000</div>
                <pre class="receipt-modal-body" id="adminReceiptModalBody"></pre>
                <button class="btn-receipt-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>

    <!-- Admin Scripts (PHP-included) -->
    <?php include 'model/ServiceModel.php'; ?>
    <?php include 'model/OrderModel.php'; ?>
    <?php include 'view/OrderView.php'; ?>
    <?php include 'view/AdminView.php'; ?>
    <?php include 'controller/AdminController.php'; ?>
</body>

</html>