<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $name      = $conn->real_escape_string(trim($_POST['name'] ?? ''));
        $price     = (float) ($_POST['price'] ?? 0);

        if ($serviceId <= 0 || $name === '') {
            echo json_encode(['success' => false, 'message' => 'Service ID and Name are required.']);
            exit;
        }

        $sql = "INSERT INTO service_variants (service_id, variant_name, base_price)
                VALUES ($serviceId, '$name', $price)";

        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'remove') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($conn->query("DELETE FROM service_variants WHERE variant_id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
