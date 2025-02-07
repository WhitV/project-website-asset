<?php
// api.php - Endpoint สำหรับ REST API

header("Content-Type: application/json; charset=UTF-8");
require_once '../models/assetModel.php';

$assetModel = new AssetModel();

$response = ["status" => "error", "message" => "Invalid request"];

// ตรวจสอบ Method ของ HTTP
$method = $_SERVER['REQUEST_METHOD'];

// ตรวจสอบ Endpoint ที่เรียกใช้งาน
if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];

    switch ($endpoint) {
        case 'getAssets':
            if ($method === 'GET') {
                $assets = $assetModel->fetchAllAssets();
                $response = ["status" => "success", "data" => $assets];
            }
            break;

        case 'getAssetById':
            if ($method === 'GET' && isset($_GET['id'])) {
                $assetId = $_GET['id'];
                $asset = $assetModel->fetchAssetById($assetId);
                if ($asset) {
                    $response = ["status" => "success", "data" => $asset];
                } else {
                    $response = ["status" => "error", "message" => "Asset not found"];
                }
            }
            break;

        case 'addAsset':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                if ($assetModel->insertAsset($data)) {
                    $response = ["status" => "success", "message" => "Asset added successfully"];
                } else {
                    $response = ["status" => "error", "message" => "Failed to add asset"];
                }
            }
            break;

        case 'deleteAsset':
            if ($method === 'DELETE' && isset($_GET['id'])) {
                $assetId = $_GET['id'];
                if ($assetModel->deleteAsset($assetId)) {
                    $response = ["status" => "success", "message" => "Asset deleted successfully"];
                } else {
                    $response = ["status" => "error", "message" => "Failed to delete asset"];
                }
            }
            break;

        case 'getUsers':
            if ($method === 'GET') {
                $users = $userModel->fetchAllUsers();
                $response = ["status" => "success", "data" => $users];
            }
            break;

        default:
            $response = ["status" => "error", "message" => "Invalid endpoint"];
            break;
    }
}

// ส่ง Response เป็น JSON
echo json_encode($response);
?>
