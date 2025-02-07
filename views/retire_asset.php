<?php
include_once '../models/assetModel.php';

// ระบุประเภทข้อมูลว่าเป็น JSON
header('Content-Type: application/json');

// จัดการข้อผิดพลาดอย่างเงียบ (log เท่านั้น)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/php-error.log'); // ตั้งค่า Log

$assetModel = new AssetModel();

// Function to handle errors
function handleError($code, $message) {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
}

// ตรวจสอบว่ามีการส่งข้อมูลแบบ POST และมี ID ของสินทรัพย์
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $assetId = $input['id'] ?? null;

    if ($assetId) {
        try {
            if ($assetModel->retireAsset($assetId)) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'เปลี่ยนสถานะสำเร็จ']);
            } else {
                throw new Exception('เกิดข้อผิดพลาดในการเปลี่ยนสถานะ');
            }
        } catch (Exception $e) {
            handleError(500, $e->getMessage());
        }
    } else {
        handleError(400, 'ไม่พบ ID ของสินทรัพย์');
    }
} else {
    handleError(405, 'Method Not Allowed');
}
?>
