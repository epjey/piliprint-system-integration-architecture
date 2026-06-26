<?php

require_once '../config/Database.php';

class Service
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM services";
        return $this->conn->query($sql);
    }
}
?>
