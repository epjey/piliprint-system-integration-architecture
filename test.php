<?php

require_once __DIR__ . '/config/Database.php';

$db = new Database();

$conn = $db->connect();

if ($conn) {
    echo "Database Connected Successfully!";
} else {
    echo "Database Connection Failed!";
}

?>