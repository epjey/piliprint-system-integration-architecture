<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

// ─── GET: fetch all services with variants + option groups ───────────────────
if ($method === 'GET') {
    $includeArchived = isset($_GET['include_archived']) && $_GET['include_archived'] == '1';
    $statusClause    = $includeArchived ? '' : "WHERE status = 'Active'";

    $result = $conn->query("SELECT * FROM services $statusClause ORDER BY service_id ASC");

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $sid = (int) $row['service_id'];

        // Variants
        $vRes     = $conn->query("SELECT * FROM service_variants WHERE service_id = $sid ORDER BY variant_id ASC");
        $variants = [];
        while ($v = $vRes->fetch_assoc()) {
            $variants[] = [
                'id'    => (int) $v['variant_id'],
                'label' => $v['variant_name'],
                'price' => (float) $v['base_price'],
            ];
        }

        // Option groups
        $oRes         = $conn->query("SELECT * FROM service_options WHERE service_id = $sid ORDER BY option_id ASC");
        $optionGroups = [];
        while ($og = $oRes->fetch_assoc()) {
            $ogId   = (int) $og['option_id'];
            $ogType = $og['option_type'];

            $items = [];
            if ($ogType === 'Page Tier') {
                $iRes = $conn->query("SELECT * FROM option_tiers WHERE option_id = $ogId ORDER BY tier_id ASC");
                while ($item = $iRes->fetch_assoc()) {
                    $items[] = [
                        'id'    => 'tier_' . $item['tier_id'],
                        'label' => $item['tier_label'],
                        'price' => (float) $item['price_addon'],
                    ];
                }
            } else {
                $iRes = $conn->query("SELECT * FROM option_choices WHERE option_id = $ogId ORDER BY choice_id ASC");
                while ($item = $iRes->fetch_assoc()) {
                    $items[] = [
                        'id'    => 'choice_' . $item['choice_id'],
                        'label' => $item['choice_label'],
                        'price' => (float) $item['price_addon'],
                    ];
                }
            }

            $optionGroups[] = [
                'id'      => $ogId,
                'label'   => $og['option_label'],
                'type'    => $ogType,
                'options' => $items,
            ];
        }

        $services[] = [
            'id'           => $sid,
            'label'        => $row['service_name'],
            'icon'         => $row['image_path'],
            'status'       => $row['status'],
            'isArchived'   => ($row['status'] !== 'Active'),
            'variants'     => $variants,
            'optionGroups' => $optionGroups,
        ];
    }

    echo json_encode(['success' => true, 'services' => $services]);
    exit;
}

// ─── POST: mutations ─────────────────────────────────────────────────────────
if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name      = $conn->real_escape_string(trim($_POST['name'] ?? ''));
        $imagePath = $conn->real_escape_string($_POST['image_path'] ?? '');

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Service name is required.']);
            exit;
        }

        $sql = "INSERT INTO services (service_name, status, image_path)
                VALUES ('$name', 'Active', " . ($imagePath === '' ? 'NULL' : "'$imagePath'") . ")";

        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'archive') {
        $id     = (int) ($_POST['id'] ?? 0);
        $status = $conn->real_escape_string($_POST['status'] ?? 'Archived');

        if ($conn->query("UPDATE services SET status = '$status' WHERE service_id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

    } elseif ($action === 'update_icon') {
        $id   = (int) ($_POST['id'] ?? 0);
        $path = $conn->real_escape_string($_POST['image_path'] ?? '');

        if ($conn->query("UPDATE services SET image_path = '$path' WHERE service_id = $id")) {
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
