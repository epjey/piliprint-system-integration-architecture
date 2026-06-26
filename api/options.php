<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_group') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $label     = $conn->real_escape_string(trim($_POST['label'] ?? ''));
        $type      = $conn->real_escape_string($_POST['type'] ?? 'Choice');

        if ($serviceId <= 0 || $label === '') {
            echo json_encode(['success' => false, 'message' => 'Service ID and Label are required.']);
            exit;
        }

        $sql = "INSERT INTO service_options (service_id, option_label, option_type)
                VALUES ($serviceId, '$label', '$type')";

        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'remove_group') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($conn->query("DELETE FROM service_options WHERE option_id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'add_item') {
        $optionId = (int) ($_POST['option_id'] ?? 0);
        $type     = $_POST['type'] ?? 'Choice'; // 'Choice' or 'Page Tier'
        $label    = $conn->real_escape_string(trim($_POST['label'] ?? ''));
        $price    = (float) ($_POST['price'] ?? 0);

        if ($optionId <= 0 || $label === '') {
            echo json_encode(['success' => false, 'message' => 'Option ID and Label are required.']);
            exit;
        }

        if ($type === 'Page Tier') {
            $sql = "INSERT INTO option_tiers (option_id, tier_label, price_addon)
                    VALUES ($optionId, '$label', $price)";
        } else {
            $sql = "INSERT INTO option_choices (option_id, choice_label, price_addon)
                    VALUES ($optionId, '$label', $price)";
        }

        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'remove_item') {
        $id   = (int) ($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'Choice'; // 'Choice' or 'Page Tier'

        if ($type === 'Page Tier') {
            $sql = "DELETE FROM option_tiers WHERE tier_id = $id";
        } else {
            $sql = "DELETE FROM option_choices WHERE choice_id = $id";
        }

        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'edit_item') {
        $id    = (int) ($_POST['id'] ?? 0);
        $type  = $_POST['type'] ?? 'Choice';
        $label = $conn->real_escape_string(trim($_POST['label'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);

        if ($id <= 0 || $label === '') {
            echo json_encode(['success' => false, 'message' => 'ID and Label are required.']);
            exit;
        }

        if ($type === 'Page Tier') {
            $sql = "UPDATE option_tiers SET tier_label = '$label', price_addon = $price WHERE tier_id = $id";
        } else {
            $sql = "UPDATE option_choices SET choice_label = '$label', price_addon = $price WHERE choice_id = $id";
        }

        if ($conn->query($sql)) {
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
