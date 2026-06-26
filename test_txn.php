<?php
$ch = curl_init('http://localhost/SIA_LAB1/api/transactions.php');
curl_setopt($ch, CURLOPT_POST, 1);
$payload = [
    'customer' => 'John Doe',
    'contact' => '09123456789',
    'paymentMethod' => 'Cash',
    'total' => 100,
    'amountPaid' => 100,
    'change' => 0,
    'items' => [
        [
            'serviceId' => 1,
            'serviceName' => 'Test',
            'variantLabel' => 'Test Variant',
            'qty' => 1,
            'unitPrice' => 100,
            'options' => []
        ]
    ]
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo "RESPONSE:\n" . $response . "\n";
