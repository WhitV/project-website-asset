<?php
// add_asset.php - หน้าเพิ่มข้อมูลสินทรัพย์
include_once '../includes/navbar.php';
include_once '../models/assetModel.php';
include_once '../models/logger.php';

$assetModel = new AssetModel();
$logger = new Logger();
$logger->log('User accessed add_asset.php');

// ดึงประเภทสินทรัพย์สำหรับ Dropdown
$assetTypes = $assetModel->fetchAssetTypes();

// กำหนดคำย่อสำหรับแผนก
$departments = [
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ค่าที่ผู้ใช้กรอก
    $assetTypeId = $_POST['asset_type_id'];
    $purchaseDate = $_POST['purchase_date'];
    $department = $_POST['department'];

    // เรียกฟังก์ชันจาก AssetModel เพื่อสร้างเลขครุภัณฑ์
    $assetCode = $assetModel->generateAssetCode($assetTypeId, $purchaseDate, $department);

    // กำหนดค่าของฟิลด์ที่ส่งไปยังฐานข้อมูล
    $data = [
        ':asset_code' => $assetCode,
        ':inventory_number' => trim(preg_replace('/\s+/', '', $_POST['inventory_number'])), // เพิ่มฟิลด์เลขครุภัณฑ์
        ':name' => $_POST['name'],
        ':asset_type_id' => $assetTypeId,
        ':serial_number' => $_POST['serial_number'],
        ':location' => !empty($_POST['location']) ? $_POST['location'] : 'ไม่ได้ระบุ',
        ':department' => $department,
        ':purchase_date' => $purchaseDate,
        ':responsible_person' => $_POST['responsible_person'],
        ':price' => $_POST['price'],
        ':image' => null, // ตั้งค่า default หากไม่มีการอัปโหลดรูปภาพ
        ':warranty_expiry_date' => $_POST['warranty_expiry_date'] ?? null,
    ];

    // การอัปโหลดรูปภาพ (ถ้ามี)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../public/uploads/';
        $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $data[':image'] = $fileName; // อัปเดตค่าของรูปภาพใน $data
            $uploadedImage = $fileName; // เก็บชื่อไฟล์รูปภาพที่อัปโหลด
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ!');</script>";
        }
    }

    // เพิ่มข้อมูลสินทรัพย์
    try {
        if ($assetModel->insertAsset($data)) {
            echo "<script>alert('เพิ่มข้อมูลสินทรัพย์สำเร็จ!'); window.location.href = 'view_assets.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มข้อมูลสินทรัพย์!');</script>";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'เลขครุภัณฑ์นี้มีอยู่แล้ว') !== false) {
            echo "<script>alert('เลขครุภัณฑ์นี้มีอยู่ในระบบแล้ว!');</script>";
        } else {
            echo "<script>alert('" . $e->getMessage() . "');</script>";
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">เพิ่มข้อมูลสินทรัพย์</h1>
    <div class="card mt-4">
        <div class="card-header">
            <h4>กรอกข้อมูลสินทรัพย์</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="inventory_number" class="form-label">เลขครุภัณฑ์<span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="inventory_number" name="inventory_number" placeholder="กรอกเลขครุภัณฑ์" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">ชื่อสินทรัพย์<span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="กรอกชื่อสินทรัพย์" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="asset_type_id" class="form-label">ประเภทสินทรัพย์<span style="color: red;">*</span></label>
                            <select class="form-select" id="asset_type_id" name="asset_type_id" required>
                                <option value="" selected disabled>เลือกประเภทสินทรัพย์</option>
                                <?php foreach ($assetTypes as $type): ?>
                                    <option value="<?php echo $type['assets_types_id']; ?>">
                                        <?php echo htmlspecialchars($type['asset_type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>                    
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="serial_number" class="form-label">Serial Number<span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="กรอก Serial Number">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department" class="form-label">แผนก<span style="color: red;">*</span></label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="" disabled selected>เลือกแผนก</option>
                                    <?php foreach ($departments as $fullName => $abbreviation): ?>
                                        <option value="<?php echo $abbreviation; ?>"><?php echo $fullName; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="location" class="form-label">สถานที่ (ถ้ามี)</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="กรอกสถานที่">
                        </div>
                    </div>
                </div>

                <div class="row"> 
                    <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsible_person" class="form-label">ผู้รับผิดชอบ<span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="responsible_person" name="responsible_person" placeholder="กรอกชื่อผู้รับผิดชอบ" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price" class="form-label">ราคา<span style="color: red;">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="กรอกราคา" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                            <div class="mb-3">
                                <label for="purchase_date" class="form-label">วันที่ซื้อ<span style="color: red;">*</span></label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" max="<?php echo date('Y-m-d', strtotime('now', strtotime('+7 hours'))); ?>" required>
                            </div>
                        </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="warranty_expiry_date" class="form-label">วันที่หมดอายุประกันสินค้า (ถ้ามี)</label>
                            <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date">
                        </div>
                    </div>
                </div>  

                <div class="mb-3">
                    <label for="image" class="form-label">อัปโหลดรูปภาพ (ถ้ามี)</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                </div>

                <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                    <label class="form-label">รูปภาพที่อัปโหลด:</label>
                    <img id="imagePreview" class="img-fluid" style="max-width: 150px; max-height: 150px;">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
                    <a href="view_assets.php" class="btn btn-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>

<script>
function previewImage(event) {
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const file = event.target.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreviewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        imagePreviewContainer.style.display = 'none';
    }
}
</script>
