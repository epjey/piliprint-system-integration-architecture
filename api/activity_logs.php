<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT log_id, user_id, role, action, details, timestamp FROM activity_log ORDER BY timestamp DESC";
    $result = $conn->query($sql);
    
    $logs = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dateObj = new DateTime($row['timestamp']);
            $logs[] = [
                'id' => $row['log_id'],
                'date' => $dateObj->format('Y-m-d'),
                'time' => $dateObj->format('h:i A'),
                'role' => $row['role'],
                'action' => $row['action'],
                'details' => $row['details']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'logs' => $logs]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
