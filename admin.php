<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PrintMaster POS - Admin</title>
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
                <div class="brand-sub">Admin Dashboard</div>
            </div>
        </div>
        <!-- Right: datetime + buttons -->
        <div style="display:flex;align-items:center;gap:8px;">
            <div class="navbar-datetime" id="navDatetime"></div>
            <button class="btn btn-txn-history" id="btnGoToPOS">Cashier POS</button>
            <button class="btn btn-logout" id="btnLogout">Logout</button>
        </div>
    </nav>

    <!-- ===== ADMIN LAYOUT ===== -->
    <div class="admin-layout">
        <!-- LEFT: Sidebar Navigation -->
        <div class="admin-sidebar">
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
                <li class="admin-nav-item" data-target="users">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Users
                </li>
                <li class="admin-nav-item" data-target="settings">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Settings
                </li>
            </ul>
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
    
    <!-- Admin Scripts -->
    <script src="model/OrderModel.php"></script>
    <script src="view/AdminView.php"></script>
    <script src="controller/AdminController.php"></script>
</body>

</html>
