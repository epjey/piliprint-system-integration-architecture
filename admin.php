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

    <!-- Admin Scripts (PHP-included) -->
    <?php include 'model/ServiceModel.php'; ?>
    <?php include 'model/OrderModel.php'; ?>
    <?php include 'view/AdminView.php'; ?>
    <?php include 'controller/AdminController.php'; ?>
</body>

</html>