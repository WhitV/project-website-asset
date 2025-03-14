<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: ../views/pin.php');
    exit();
}

include_once '../models/logger.php';
$logger = new Logger();
$logger->log('User accessed download_warranty_alerts.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="warranty_alerts.csv"');

require_once '../config/config.php';
$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set UTF-8 encoding for the connection
$connection->set_charset('utf8');

$output = fopen('php://output', 'w');

// Add BOM to fix UTF-8 in Excel
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

fputcsv($output, array('ID', 'Asset Code', 'Inventory Number', 'Name', 'Asset Type', 'Serial Number', 'Location', 'Department', 'Purchase Date', 'Responsible Person', 'Price', 'Created At', 'Updated At', 'Status', 'Image', 'Warranty Expiry Date'));

$query = "
    SELECT 
        a.id, 
        a.asset_code, 
        a.inventory_number, 
        a.name, 
        at.asset_type_name, 
        a.serial_number, 
        a.location, 
        a.department, 
        a.purchase_date, 
        a.responsible_person, 
        a.price, 
        a.created_at, 
        a.updated_at, 
        a.status, 
        a.image, 
        a.warranty_expiry_date 
    FROM assets a 
    JOIN asset_types at ON a.asset_type_id = at.assets_types_id 
    WHERE a.status = 'Active' 
    AND a.warranty_expiry_date IS NOT NULL 
    AND a.warranty_expiry_date != '0000-00-00' 
    AND a.warranty_expiry_date BETWEEN CURDATE() AND CURDATE() + INTERVAL 1 MONTH
";
$result = $connection->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    echo "Error fetching data: " . $connection->error;
}

fclose($output);
$connection->close();
?>
