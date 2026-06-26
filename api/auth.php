<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email    = $conn->real_escape_string(trim($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit;
        }

        // NOTE: Real implementation should use password_hash and password_verify
        // Since user manually inserted raw '1234', we match raw text for now
        $sql    = "SELECT user_id, role, password FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Checking raw password matching
            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role']    = $user['role'];
                
                echo json_encode([
                    'success'  => true,
                    'user_id'  => $user['user_id'],
                    'role'     => $user['role'],
                    'redirect' => $user['role'] === 'Admin' ? 'admin.php' : 'index.php'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
