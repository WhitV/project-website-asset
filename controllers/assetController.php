<?php
// assetController.php - ควบคุมการทำงานที่เกี่ยวกับสินทรัพย์

include_once '../models/assetModel.php';
session_start();

class AssetController {
    private $assetModel;

    /**
     * Constructor: สร้าง object ของ AssetModel เพื่อใช้ในการเชื่อมต่อฐานข้อมูล
     */
    public function __construct() {
        $this->assetModel = new AssetModel();
    }

    /**
     * ฟังก์ชันเพิ่มสินทรัพย์ใหม่
     * @param array $data ข้อมูลสินทรัพย์
     * @return array ผลลัพธ์การดำเนินการ
     */
    public function addAsset($data) {
        try {
            return $this->assetModel->insertAsset($data);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันแก้ไขข้อมูลสินทรัพย์
     * @param array $data ข้อมูลสินทรัพย์
     * @return array ผลลัพธ์การดำเนินการ
     */
    public function editAsset($data) {
        try {
            return $this->assetModel->updateAsset($data);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันลบสินทรัพย์
     * @param int $id รหัสสินทรัพย์
     * @return array ผลลัพธ์การดำเนินการ
     */
    public function deleteAsset($id) {
        try {
            return $this->assetModel->deleteAsset($id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันดึงสินทรัพย์ทั้งหมด
     * @return array ข้อมูลสินทรัพย์ทั้งหมด
     */
    public function getAllAssets() {
        try {
            return $this->assetModel->fetchAllAssets();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันดึงข้อมูลสินทรัพย์ตาม ID
     * @param int $id รหัสสินทรัพย์
     * @return array ข้อมูลสินทรัพย์
     */
    public function getAssetById($id) {
        try {
            return $this->assetModel->fetchAssetById($id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันดึงประเภทสินทรัพย์ทั้งหมด
     * @return array รายการประเภทสินทรัพย์
     */
    public function getAssetTypes() {
        try {
            return $this->assetModel->fetchAssetTypes();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันเพิ่มประเภทสินทรัพย์
     * @param string $name ชื่อประเภทสินทรัพย์
     * @return array ผลลัพธ์การดำเนินการ
     */
    public function addAssetType($name) {
        try {
            return $this->assetModel->insertAssetType($name);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ฟังก์ชันลบประเภทสินทรัพย์
     * @param int $id รหัสประเภทสินทรัพย์
     * @return array ผลลัพธ์การดำเนินการ
     */
    public function deleteAssetType($id) {
        try {
            return $this->assetModel->deleteAssetType($id);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
