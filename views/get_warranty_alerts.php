<?php
require_once '../config/config.php';

$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$query = "
    SELECT 
        id, 
        name, 
        warranty_expiry_date 
    FROM assets 
    WHERE status = 'Active' 
    AND warranty_expiry_date IS NOT NULL 
    AND warranty_expiry_date != '0000-00-00' 
    AND warranty_expiry_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 MONTH
";
$result = $connection->query($query);

$alerts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }
}

echo json_encode($alerts);

$connection->close();
?>
