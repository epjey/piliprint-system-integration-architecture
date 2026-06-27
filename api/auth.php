<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

$db = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit;
        }


        // Since user manually inserted raw '1234', we match raw text for now
        $sql = "SELECT user_id, role, password, status FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Checking raw password matching
            if ($password === $user['password']) {
                if (isset($user['status']) && $user['status'] === 'Inactive') {
                    echo json_encode(['success' => false, 'message' => 'Account is inactive. Please contact administrator.']);
                    exit;
                }
                $prefix = strtolower($user['role']) . '_';
                $_SESSION[$prefix . 'user_id'] = $user['user_id'];
                $_SESSION[$prefix . 'role'] = $user['role'];
                $_SESSION[$prefix . 'email'] = $email;

                $escapedEmail = $conn->real_escape_string($email);
                $logSql = "INSERT INTO activity_log (user_id, role, action, details) VALUES (" . $user['user_id'] . ", '" . $user['role'] . "', 'Login', '$escapedEmail logged in to the system')";
                $conn->query($logSql);

                echo json_encode([
                    'success' => true,
                    'user_id' => $user['user_id'],
                    'role' => $user['role'],
                    'redirect' => $user['role'] === 'Admin' ? 'admin.php' : 'index.php'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } elseif ($action === 'logout') {
        $roleToLogout = $_POST['role'] ?? '';
        if ($roleToLogout === 'Admin' || $roleToLogout === 'Cashier') {
            $prefix = strtolower($roleToLogout) . '_';
            if (isset($_SESSION[$prefix . 'user_id'])) {
                $userId = $_SESSION[$prefix . 'user_id'];
                $logEmail = $conn->real_escape_string($_SESSION[$prefix . 'email'] ?? 'User');
                $logSql = "INSERT INTO activity_log (user_id, role, action, details) VALUES ($userId, '$roleToLogout', 'Logout', '$logEmail logged out of the system')";
                $conn->query($logSql);

                unset($_SESSION[$prefix . 'user_id']);
                unset($_SESSION[$prefix . 'role']);
                unset($_SESSION[$prefix . 'email']);
            }
        }
        echo json_encode(['success' => true]);
    } elseif ($action === 'check_status') {
        $roleToCheck = $_POST['role'] ?? 'Cashier';
        $prefix = strtolower($roleToCheck) . '_';

        if (isset($_SESSION[$prefix . 'user_id'])) {
            $userId = (int) $_SESSION[$prefix . 'user_id'];
            $res = $conn->query("SELECT status FROM users WHERE user_id = $userId");
            if ($res && $res->num_rows > 0) {
                $user = $res->fetch_assoc();
                if ($user['status'] === 'Inactive') {
                    unset($_SESSION[$prefix . 'user_id']);
                    unset($_SESSION[$prefix . 'role']);
                    unset($_SESSION[$prefix . 'email']);
                    echo json_encode(['success' => true, 'inactive' => true]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => true, 'inactive' => false]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
