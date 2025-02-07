<?php
// assetModel.php - Functions related to asset management

include_once 'database.php';

class AssetModel {
    private $db;

    // Constructor to initialize database connection
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Insert a new asset into the database.
     */
    public function insertAsset($data) {
        try {
            // Normalize inventory_number by trimming whitespace
            $data[':inventory_number'] = trim(preg_replace('/\s+/', '', $data[':inventory_number']));

            // Check if inventory_number already exists
            $query = "SELECT COUNT(*) FROM assets WHERE TRIM(REPLACE(inventory_number, ' ', '')) = :inventory_number";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':inventory_number', $data[':inventory_number'], PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                throw new Exception('เลขครุภัณฑ์นี้มีอยู่แล้ว');
            }

            $query = "INSERT INTO assets (
                          asset_code, 
                          inventory_number, 
                          name, 
                          asset_type_id, 
                          serial_number, 
                          location, 
                          department, 
                          purchase_date, 
                          responsible_person, 
                          price, 
                          image, 
                          status
                      ) VALUES (
                          :asset_code, 
                          :inventory_number, 
                          :name, 
                          :asset_type_id, 
                          :serial_number, 
                          :location, 
                          :department, 
                          :purchase_date, 
                          :responsible_person, 
                          :price, 
                          :image, 
                          'Active'
                      )";

            $stmt = $this->db->prepare($query);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error code
                throw new Exception('รหัสสินทรัพย์นี้มีอยู่แล้ว');
            } else {
                throw new Exception('เกิดข้อผิดพลาดในการเพิ่มสินทรัพย์: ' . $e->getMessage());
            }
        }
    }

    /**
     * Update an existing asset in the database.
     */
    public function updateAsset($data) {
        $query = "UPDATE assets SET 
                      name = :name, 
                      asset_type_id = :asset_type_id, 
                      serial_number = :serial_number, 
                      location = :location, 
                      department = :department, 
                      purchase_date = :purchase_date, 
                      responsible_person = :responsible_person, 
                      price = :price, 
                      image = :image,
                      inventory_number = :inventory_number
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if (!$stmt->execute($data)) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception('Failed to update asset: ' . $errorInfo[2]);
        }

        return true;
    }

    /**
     * Delete an asset by ID.
     */
    public function deleteAsset($id) {
        $query = "DELETE FROM assets WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception('Failed to delete asset: ' . $errorInfo[2]);
        }

        return true;
    }

    /**
     * Fetch all assets with their associated asset types.
     */
    public function fetchAllAssets() {
        $query = "SELECT 
                    assets.id, 
                    assets.asset_code, 
                    assets.inventory_number, 
                    assets.name, 
                    asset_types.asset_type_name AS asset_type_name, 
                    assets.serial_number, 
                    assets.location, 
                    assets.department, 
                    assets.purchase_date, 
                    assets.price, 
                    assets.status, 
                    assets.image, 
                    assets.responsible_person
                  FROM assets 
                  LEFT JOIN asset_types ON assets.asset_type_id = asset_types.assets_types_id 
                  ORDER BY assets.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch an asset by ID.
     */
    public function fetchAssetById($id) {
        $query = "SELECT assets.*, asset_types.asset_type_name AS asset_type_name 
                  FROM assets 
                  LEFT JOIN asset_types ON assets.asset_type_id = asset_types.assets_types_id
                  WHERE assets.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all asset types, optionally including hidden types.
     */
    public function fetchAssetTypes($includeHidden = false) {
        $query = "SELECT * FROM asset_types";
        if (!$includeHidden) {
            $query .= " WHERE asset_type_hidden = 0";
        }
        $query .= " ORDER BY assets_types_id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a new asset type.
     */
    public function insertAssetType($name) {
        $query = "INSERT INTO asset_types (asset_type_name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception('Failed to insert asset type: ' . $errorInfo[2]);
        }

        return true;
    }

    /**
     * Add or update an asset type. If the asset type already exists and is hidden, make it visible.
     */
    public function addOrUpdateAssetType($name) {
        try {
            // Check if the asset type already exists
            $query = "SELECT * FROM asset_types WHERE asset_type_name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->execute();
            $existingType = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingType) {
                // If the asset type exists and is hidden, make it visible
                if ($existingType['asset_type_hidden'] == 1) {
                    $query = "UPDATE asset_types SET asset_type_hidden = 0 WHERE assets_types_id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(':id', $existingType['assets_types_id'], PDO::PARAM_INT);
                    return $stmt->execute();
                } else {
                    throw new Exception('ชื่อประเภทสินทรัพย์นี้มีอยู่แล้ว');
                }
            } else {
                // If the asset type does not exist, insert a new one
                $query = "INSERT INTO asset_types (asset_type_name) VALUES (:name)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                return $stmt->execute();
            }
        } catch (PDOException $e) {
            throw new Exception('เกิดข้อผิดพลาดในการเพิ่มประเภทสินทรัพย์: ' . $e->getMessage());
        }
    }

    /**
     * Hide an asset type if there are no active assets associated with it.
     */
    public function hideAssetType($id) {
        // Check if there are any active assets associated with this type
        $query = "SELECT COUNT(*) AS count FROM assets WHERE asset_type_id = :id AND status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($count > 0) {
            throw new Exception('ไม่สามารถซ่อนประเภทสินทรัพย์ได้เนื่องจากมีสินทรัพย์ที่เกี่ยวข้อง!');
        }

        // Hide the asset type
        $query = "UPDATE asset_types SET asset_type_hidden = 1 WHERE assets_types_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get total number of active assets.
     */
    public function getTotalAssets() {
        $query = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get total number of retired assets.
     */
    public function getRetiredAssets() {
        $query = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Retired'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get asset count grouped by type.
     */
    public function getAssetCountByType() {
        $query = "SELECT asset_types.asset_type_name AS name, COUNT(assets.id) AS count 
                  FROM assets 
                  LEFT JOIN asset_types ON assets.asset_type_id = asset_types.assets_types_id
                  GROUP BY asset_types.asset_type_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get total price of all active assets.
     */
    public function getTotalAssetPrice() {
        $query = "SELECT SUM(price) AS total_price FROM assets WHERE status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total_price'];
    }

    /**
     * Get the oldest asset by purchase date.
     */
    public function getOldestAsset() {
        $query = "SELECT name, purchase_date 
                  FROM assets 
                  ORDER BY purchase_date ASC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get the newest asset by purchase date.
     */
    public function getNewestAsset() {
        $query = "SELECT name, purchase_date 
                  FROM assets 
                  ORDER BY purchase_date DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get total depreciated value of active assets.
     */
    public function getDepreciatedAssetValue() {
        $query = "SELECT SUM(price * (1 - (DATEDIFF(CURDATE(), purchase_date) / (5 * 365)))) AS depreciated_value
                  FROM assets
                  WHERE DATEDIFF(CURDATE(), purchase_date) <= (5 * 365) AND status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['depreciated_value'];
    }
    
    public function addAssetType($name) {
        try {
            $query = "INSERT INTO asset_types (asset_type_name) VALUES (:name)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error code
                throw new Exception('ชื่อประเภทสินทรัพย์นี้มีอยู่แล้ว');
            } else {
                throw new Exception('เกิดข้อผิดพลาดในการเพิ่มประเภทสินทรัพย์: ' . $e->getMessage());
            }
        }
    }

    public function getAssetPriceByMonth() {
        $query = "SELECT DATE_FORMAT(purchase_date, '%Y-%m') AS month, SUM(price) AS total_price 
                  FROM assets 
                  GROUP BY DATE_FORMAT(purchase_date, '%Y-%m') 
                  ORDER BY month ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // จัดรูปแบบผลลัพธ์เป็น array ของ key-value
        $prices = [];
        foreach ($result as $row) {
            $prices[$row['month']] = $row['total_price'];
        }
    
        return $prices;
    }

    public function getNextAssetSequence($assetTypeId) {
        $query = "SELECT COUNT(*) + 1 AS next_sequence FROM assets WHERE asset_type_id = :asset_type_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':asset_type_id', $assetTypeId, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC)['next_sequence'] ?? 1; // ถ้าไม่มีสินทรัพย์ในประเภทนี้ ให้เริ่มที่ 1
    }
    
    public function generateAssetCode($assetTypeId, $purchaseDate, $department) {
        // ดึงชื่อประเภทสินทรัพย์
        $query = "SELECT asset_type_name FROM asset_types WHERE assets_types_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $assetTypeId, PDO::PARAM_INT);
        $stmt->execute();
        $assetTypeName = $stmt->fetch(PDO::FETCH_ASSOC)['asset_type_name'] ?? null;
    
        if (!$assetTypeName) {
            throw new Exception("ไม่พบประเภทสินทรัพย์ที่ระบุ");
        }
    
        // สร้างคำย่อ
        $assetTypeAbbreviation = $this->getAbbreviation($assetTypeName);
        $departmentAbbreviation = $this->getAbbreviation($department);
    
        // ดึงลำดับถัดไป
        $sequence = $this->getNextAssetSequence($assetTypeId);
    
        // สร้างรหัสครุภัณฑ์
        return strtoupper("{$assetTypeAbbreviation}-{$departmentAbbreviation}-" . date('dmY', strtotime($purchaseDate)) . "-{$sequence}");
    }
    
    public function getAbbreviation($fullName) {
        // กำหนดคำย่อแบบ Custom
        $abbreviationMapping = [
            'แผนกบุคคล (Human Resources)' => 'HR',
            'แผนกการเงิน (Finance)' => 'FIN',
            'แผนกการตลาด (Marketing)' => 'MKT',
            'แผนกไอที (Information Technology)' => 'IT',
            'แผนกผลิตหรือการดำเนินงาน (Production/Operations)' => 'PO',
            'แผนกบริการลูกค้า (Customer Service)' => 'CS',
            'แผนกจัดซื้อ (Procurement)' => 'PRC',
            'แผนกการจัดการข้อมูล (Data Management)' => 'DM',
            'แผนกการพัฒนาธุรกิจ (Business Development)' => 'BD',
            'แผนกขาย (Sales)' => 'SALES',
        ];
    
        // ตรวจสอบว่า $fullName มีใน Mapping หรือไม่
        return $abbreviationMapping[$fullName] ?? strtoupper(substr($fullName, 0, 3)); // ถ้าไม่พบ ใช้อักษรแรก 3 ตัวอักษร
    }

    public function deleteAssetType($id) {
        $query = "DELETE FROM asset_types WHERE assets_types_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function retireAsset($id) {
        try {
            $query = "UPDATE assets SET status = 'Retired' WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                error_log('Database error in retireAsset: ' . $errorInfo[2]);
                throw new Exception('Database error: ' . $errorInfo[2]);
            }
    
            return true;
        } catch (Exception $e) {
            error_log('Error in retireAsset: ' . $e->getMessage());
            return false;
        }
    }
    
    
    public function updateAssetStatus($id, $status) {
        $query = "UPDATE assets SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
        return $stmt->execute();
    }    
    
    public function fetchAssetByNameAndType($name, $type_id) {
        $sql = "SELECT * FROM assets WHERE name = :name AND asset_type_id = :type_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type_id', $type_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get the count of assets associated with a specific asset type.
     */
    public function getAssetCountByTypeId($typeId) {
        $query = "SELECT COUNT(*) AS count FROM assets WHERE asset_type_id = :type_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Update the status of all assets associated with a specific asset type to 'Retired'.
     */
    public function retireAssetsByTypeId($typeId) {
        $query = "UPDATE assets SET status = 'Retired' WHERE asset_type_id = :type_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':type_id', $typeId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
}
?>
