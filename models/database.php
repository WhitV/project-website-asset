<?php
// database.php - การเชื่อมต่อฐานข้อมูล

class Database {
    private $host = 'localhost';
    private $dbName = 'asset_management_DB'; // ชื่อฐานข้อมูลที่เราตกลงใช้
    private $username = 'root'; // Username ของฐานข้อมูล (ปรับตามการตั้งค่าของคุณ)
    private $password = ''; // Password ของฐานข้อมูล (ปรับตามการตั้งค่าของคุณ)
    public $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $this->connection = new PDO("mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
?>
