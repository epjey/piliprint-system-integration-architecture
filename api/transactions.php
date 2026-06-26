<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';

$db   = new Database();
$conn = $db->connect();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all transactions
    $sql = "SELECT * FROM transactions ORDER BY transaction_id DESC";
    $result = $conn->query($sql);
    $transactions = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $txn_id = $row['transaction_id'];
            
            // Format orderNum
            $orderNum = str_pad($txn_id, 4, '0', STR_PAD_LEFT);
            
            // Fetch items for this transaction
            $itemsSql = "SELECT * FROM transaction_items WHERE transaction_id = $txn_id";
            $itemsResult = $conn->query($itemsSql);
            $items = [];
            if ($itemsResult && $itemsResult->num_rows > 0) {
                while ($itemRow = $itemsResult->fetch_assoc()) {
                    $items[] = [
                        'serviceName' => $itemRow['service_name'],
                        'variantLabel' => $itemRow['variant_name'],
                        'qty' => (int)$itemRow['quantity'],
                        'unitPrice' => (float)$itemRow['price_at_time'],
                        'options' => json_decode($itemRow['item_details'], true) ?: []
                    ];
                }
            }
            
            // Format date and time
            $dateObj = new DateTime($row['created_at']);
            
            $transactions[] = [
                'orderNum' => $orderNum,
                'customer' => $row['customer_name'],
                'contact' => $row['contact_number'],
                'paymentMethod' => $row['payment_method'],
                'total' => (float)$row['total_amount'],
                'amountPaid' => (float)$row['cash_amount'],
                'change' => (float)$row['change_amount'],
                'date' => $dateObj->format('Y-m-d'),
                'time' => $dateObj->format('h:i A'),
                'items' => $items
            ];
        }
    }
    echo json_encode(['success' => true, 'transactions' => $transactions]);
    exit;
}

if ($method === 'POST') {
    // We expect JSON payload for placing an order
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
        exit;
    }
    
    $userId = $_SESSION['user_id'] ?? null; 
    
    // Fallback if not logged in (e.g. Kiosk mode)
    if (!$userId) {
        $userRes = $conn->query("SELECT user_id, role FROM users LIMIT 1");
        if ($userRes && $userRes->num_rows > 0) {
            $row = $userRes->fetch_assoc();
            $userId = $row['user_id'];
            if (!isset($_SESSION['role'])) {
                $_SESSION['role'] = $row['role'];
            }
        } else {
            // Failsafe if absolutely no users exist in the database
            $conn->query("INSERT IGNORE INTO users (user_id, email, password, role) VALUES (1, 'fallback@system.com', '1234', 'Cashier')");
            $userId = 1;
            $_SESSION['role'] = 'Cashier';
        }
    }
    $customerName = $conn->real_escape_string($data['customer'] ?? '');
    $contactNumber = $conn->real_escape_string($data['contact'] ?? '');
    $paymentMethod = $conn->real_escape_string($data['paymentMethod'] ?? '');
    $totalAmount = (float)($data['total'] ?? 0);
    $cashAmount = (float)($data['amountPaid'] ?? 0);
    $changeAmount = (float)($data['change'] ?? 0);
    
    $conn->begin_transaction();
    
    try {
        // Insert transaction
        $sql = "INSERT INTO transactions (user_id, customer_name, contact_number, payment_method, total_amount, cash_amount, change_amount) 
                VALUES ($userId, '$customerName', '$contactNumber', '$paymentMethod', $totalAmount, $cashAmount, $changeAmount)";
                
        if (!$conn->query($sql)) {
            throw new Exception("Error inserting transaction: " . $conn->error);
        }
        
        $transactionId = $conn->insert_id;
        $orderNum = str_pad($transactionId, 4, '0', STR_PAD_LEFT);
        
        // Insert items
        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $serviceId = isset($item['serviceId']) ? (int)$item['serviceId'] : 'NULL';
                $serviceName = $conn->real_escape_string($item['serviceName'] ?? '');
                $variantName = $conn->real_escape_string($item['variantLabel'] ?? '');
                $itemDetails = $conn->real_escape_string(json_encode($item['options'] ?? []));
                $quantity = (int)($item['qty'] ?? 1);
                $priceAtTime = (float)($item['unitPrice'] ?? 0);
                $subtotal = $priceAtTime * $quantity;
                
                $itemSql = "INSERT INTO transaction_items (transaction_id, service_id, service_name, variant_name, item_details, quantity, price_at_time, subtotal)
                            VALUES ($transactionId, $serviceId, '$serviceName', '$variantName', '$itemDetails', $quantity, $priceAtTime, $subtotal)";
                if (!$conn->query($itemSql)) {
                    throw new Exception("Error inserting item: " . $conn->error);
                }
            }
        }
        
        // Log activity
        $role = $_SESSION['role'] ?? 'Cashier';
        $logSql = "INSERT INTO activity_log (user_id, role, action, details) VALUES ($userId, '$role', 'Placed Order', 'Order #$orderNum created for ₱$totalAmount')";
        $conn->query($logSql);
        
        $conn->commit();
        
        // Return the formatted transaction just like GET
        $now = new DateTime();
        $txn = [
            'orderNum' => $orderNum,
            'customer' => $customerName,
            'contact' => $contactNumber,
            'paymentMethod' => $paymentMethod,
            'total' => $totalAmount,
            'amountPaid' => $cashAmount,
            'change' => $changeAmount,
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('h:i A'),
            'items' => $data['items']
        ];
        
        echo json_encode(['success' => true, 'transaction' => $txn]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed']);
