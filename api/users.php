<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';

// Only Admin can access
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db   = new Database();
$conn = $db->connect();

// Ensure status column exists (safe migration)
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active'");

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: List all users ──────────────────────────────────────────────────
if ($method === 'GET') {
    $sql    = "SELECT user_id, email, role, status, created_at FROM users ORDER BY user_id ASC";
    $result = $conn->query($sql);
    $users  = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $d       = new DateTime($row['created_at']);
            $users[] = [
                'id'        => (int)$row['user_id'],
                'email'     => $row['email'],
                'role'      => $row['role'],
                'status'    => $row['status'],
                'createdAt' => $d->format('F j, Y'),
            ];
        }
    }
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

// ── POST: Mutating actions ───────────────────────────────────────────────
if ($method === 'POST') {
    $raw    = file_get_contents('php://input');
    $data   = json_decode($raw, true) ?: $_POST;
    $action = $data['action'] ?? '';
    $adminId = (int)($_SESSION['admin_user_id'] ?? 0);

    // Helper to log
    $log = function($act, $det) use ($conn, $adminId) {
        $a = $conn->real_escape_string($act);
        $d = $conn->real_escape_string($det);
        $conn->query("INSERT INTO activity_log (user_id, role, action, details) VALUES ($adminId, 'Admin', '$a', '$d')");
    };

    // ── CREATE cashier ────────────────────────────────────────────────
    if ($action === 'create') {
        $email    = $conn->real_escape_string(trim($data['email']    ?? ''));
        $password = $conn->real_escape_string(trim($data['password'] ?? ''));
        $status   = in_array($data['status'] ?? 'Active', ['Active','Inactive']) ? $data['status'] : 'Active';

        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit;
        }

        $sql = "INSERT INTO users (email, password, role, status) VALUES ('$email','$password','Cashier','$status')";
        if ($conn->query($sql)) {
            $log('Create User', "Created cashier: $email");
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            $msg = strpos($conn->error, 'Duplicate') !== false ? 'Email already exists.' : $conn->error;
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit;
    }

    // ── UPDATE cashier (email + status) ──────────────────────────────
    if ($action === 'update') {
        $id     = (int)($data['id']     ?? 0);
        $email  = $conn->real_escape_string(trim($data['email']  ?? ''));
        $status = in_array($data['status'] ?? 'Active', ['Active','Inactive']) ? $data['status'] : 'Active';

        if ($id <= 0 || !$email) {
            echo json_encode(['success' => false, 'message' => 'ID and Email are required.']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit;
        }

        $sql = "UPDATE users SET email='$email', status='$status' WHERE user_id=$id AND role='Cashier'";
        if ($conn->query($sql)) {
            $log('Edit User', "Updated details for cashier: $email");
            echo json_encode(['success' => true]);
        } else {
            $msg = strpos($conn->error, 'Duplicate') !== false ? 'Email already exists.' : $conn->error;
            echo json_encode(['success' => false, 'message' => $msg]);
        }
        exit;
    }

    // ── RESET PASSWORD ────────────────────────────────────────────────
    if ($action === 'reset_password') {
        $id       = (int)($data['id']       ?? 0);
        $password = $conn->real_escape_string(trim($data['password'] ?? ''));

        if ($id <= 0 || !$password) {
            echo json_encode(['success' => false, 'message' => 'ID and password are required.']);
            exit;
        }

        // Fetch email for readable log
        $r = $conn->query("SELECT email FROM users WHERE user_id=$id AND role='Cashier'");
        $email = $r && $r->num_rows > 0 ? $r->fetch_assoc()['email'] : 'Unknown';

        $sql = "UPDATE users SET password='$password' WHERE user_id=$id AND role='Cashier'";
        if ($conn->query($sql)) {
            $log('Reset Password', "Reset password for cashier: $email");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    // ── TOGGLE STATUS ─────────────────────────────────────────────────
    if ($action === 'toggle_status') {
        $id        = (int)($data['id']     ?? 0);
        $newStatus = in_array($data['status'] ?? '', ['Active','Inactive']) ? $data['status'] : 'Active';

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID is required.']);
            exit;
        }

        // Fetch email for readable log
        $r = $conn->query("SELECT email FROM users WHERE user_id=$id AND role='Cashier'");
        $email = $r && $r->num_rows > 0 ? $r->fetch_assoc()['email'] : 'Unknown';

        $sql = "UPDATE users SET status='$newStatus' WHERE user_id=$id AND role='Cashier'";
        if ($conn->query($sql)) {
            $log('Toggle Status', "Set cashier $email to $newStatus");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    // ── DELETE cashier (never admin) ──────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID is required.']);
            exit;
        }
        // Guard: fetch email for log before deleting
        $r    = $conn->query("SELECT email FROM users WHERE user_id=$id AND role='Cashier'");
        $uRow = $r ? $r->fetch_assoc() : null;
        if (!$uRow) {
            echo json_encode(['success' => false, 'message' => 'Cashier not found or cannot delete Admin.']);
            exit;
        }
        $email = $uRow['email'];
        if ($conn->query("DELETE FROM users WHERE user_id=$id AND role='Cashier'")) {
            $log('Delete User', "Deleted cashier: $email");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cannot delete user. They might have associated transactions or logs.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
