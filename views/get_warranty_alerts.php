<?php
include_once '../models/assetModel.php';

$assetModel = new AssetModel();
$assets = $assetModel->fetchActiveAssetsNearWarrantyExpiry();

echo json_encode($assets);
?>
