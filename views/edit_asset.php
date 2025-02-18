<?php
// edit_asset.php - หน้าแก้ไขข้อมูลสินทรัพย์
include_once '../includes/navbar.php';
include_once '../models/assetModel.php';

$assetModel = new AssetModel();

// ตรวจสอบว่ามี ID ถูกส่งมา
if (!isset($_GET['id'])) {
    echo "<script>alert('ไม่พบข้อมูลสินทรัพย์!'); window.location.href = 'view_assets.php';</script>";
    exit;
}

$assetId = $_GET['id'];

// ดึงข้อมูลสินทรัพย์
$asset = $assetModel->fetchAssetById($assetId);
if (!$asset) {
    echo "<script>alert('ไม่พบข้อมูลสินทรัพย์!'); window.location.href = 'view_assets.php';</script>";
    exit;
}

// ดึงประเภทสินทรัพย์สำหรับ Dropdown
$assetTypes = $assetModel->fetchAssetTypes();

// หาชื่อประเภทสินทรัพย์ปัจจุบัน
$currentTypeName = '';
foreach ($assetTypes as $type) {
    if ($type['assets_types_id'] == $asset['asset_type_id']) {
        $currentTypeName = htmlspecialchars($type['asset_type_name']);
        break;
    }
}

// รายการแผนก
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

// กำหนดตัวแปรเพื่อควบคุมการแสดงฟอร์มแก้ไข
$editMode = false;

// ตรวจสอบว่ามีการยืนยันข้อมูลหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_name']) && isset($_POST['confirm_type'])) {
        // ขั้นตอนการยืนยันชื่อและประเภทสินทรัพย์
        $confirmName = trim($_POST['confirm_name']);
        $confirmType = $_POST['confirm_type'];

        // ตรวจสอบว่าชื่อสินทรัพย์และประเภทสินทรัพย์ตรงกันกับข้อมูลในฐานข้อมูล
        if ($confirmName === $asset['name'] && $confirmType == $currentTypeName) {
            $editMode = true; // เปิดโหมดแก้ไขข้อมูล
        } else {
            echo "<script>alert('ชื่อสินทรัพย์หรือประเภทสินทรัพย์ไม่ถูกต้อง!');</script>";
        }
    } elseif (isset($_POST['edit_submit'])) {
        // ขั้นตอนการแก้ไขข้อมูลสินทรัพย์

        // เก็บข้อมูลจากฟอร์ม
        $data = [
            ':id' => $assetId,
            ':name' => $_POST['name'],
            ':asset_type_id' => $_POST['asset_type_id'],
            ':serial_number' => $_POST['serial_number'],
            ':location' => $_POST['location'],
            ':department' => $_POST['department'],
            ':purchase_date' => $_POST['purchase_date'],
            ':responsible_person' => $_POST['responsible_person'],
            ':price' => $_POST['price'],
            ':inventory_number' => $_POST['inventory_number'], // เพิ่มฟิลด์เลขครุภัณฑ์
            ':image' => $asset['image'],
            ':warranty_expiry_date' => $_POST['warranty_expiry_date'] ?? null,
        ];

        // การอัปโหลดไฟล์รูปภาพใหม่ (ถ้ามี)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../public/uploads/';
            $fileName = uniqid() . '-' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $data[':image'] = $fileName;
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ!');</script>";
            }
        }

        // อัปเดตข้อมูลสินทรัพย์
        if ($assetModel->updateAsset($data)) {
            echo "<script>alert('แก้ไขข้อมูลสินทรัพย์สำเร็จ!'); window.location.href = 'view_assets.php';</script>";
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล!');</script>";
        }
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">แก้ไขข้อมูลสินทรัพย์</h1>
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h4>กรอกข้อมูลสินทรัพย์</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <?php if (!$editMode): ?>
                    <!-- ฟอร์มยืนยันชื่อและประเภทสินทรัพย์ -->
                    <div class="mb-3">
                        <label class="form-label">
                            ยืนยันชื่อสินทรัพย์: <strong><?php echo htmlspecialchars($asset['name']); ?></strong>
                        </label>
                        <input type="text" class="form-control" id="confirm_name" name="confirm_name" placeholder="กรอกชื่อสินทรัพย์เพื่อยืนยัน" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            ยืนยันประเภทสินทรัพย์: <strong><?php echo htmlspecialchars($currentTypeName); ?></strong>
                        </label>
                        <input type="text" class="form-control" id="confirm_type" name="confirm_type" placeholder="กรอกประเภทสินทรัพย์เพื่อยืนยัน" required>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-warning">เปิดการแก้ไข</button>
                        <a href="view_assets.php" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                <?php else: ?>
                    <!-- ฟอร์มแก้ไขข้อมูลสินทรัพย์ -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inventory_number" class="form-label" >เลขครุภัณฑ์<span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="inventory_number" name="inventory_number" value="<?php echo htmlspecialchars($asset['inventory_number']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label" >ชื่อสินทรัพย์<span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($asset['name']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="asset_type_id" class="form-label" >ประเภทสินทรัพย์<span style="color: red;">*</span></label>
                                <select class="form-select" id="asset_type_id" name="asset_type_id" required>
                                    <option value="">เลือกประเภทสินทรัพย์</option>
                                    <?php foreach ($assetTypes as $type): ?>
                                        <option value="<?php echo $type['assets_types_id']; ?>" <?php echo $asset['asset_type_id'] == $type['assets_types_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['asset_type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="serial_number" class="form-label" >Serial Number<span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($asset['serial_number']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="department" class="form-label">แผนก<span style="color: red;">*</span></label>
                                <select class="form-select" id="department" name="department" required>
                                    <?php foreach ($departments as $fullName => $abbreviation): ?>
                                        <option value="<?php echo $abbreviation; ?>" <?php echo ($asset['department'] == $abbreviation) ? 'selected' : ''; ?>>
                                            <?php echo $fullName; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location" class="form-label" >สถานที่ (ถ้ามี)</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($asset['location']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="purchase_date" class="form-label" >วันที่ซื้อ<span style="color: red;">*</span></label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo htmlspecialchars($asset['purchase_date']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="responsible_person" class="form-label">ผู้รับผิดชอบ<span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="responsible_person" name="responsible_person" value="<?php echo htmlspecialchars($asset['responsible_person']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">ราคา<span style="color: red;">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($asset['price']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="warranty_expiry_date" class="form-label">วันที่หมดอายุประกันสินค้า</label>
                                <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date" value="<?php echo htmlspecialchars($asset['warranty_expiry_date']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รูปภาพปัจจุบัน: (ถ้ามี)</label>
                        <?php if (!empty($asset['image'])): ?>
                            <img src="../public/uploads/<?php echo htmlspecialchars($asset['image']); ?>" alt="Asset Image" style="max-width: 150px; max-height: 150px;">
                        <?php else: ?>
                            <p>ไม่มีรูปภาพ</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">อัปโหลดรูปภาพใหม่:</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                    </div>

                    <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                        <label class="form-label">รูปภาพที่อัปโหลด:</label>
                        <img id="imagePreview" class="img-fluid" style="max-width: 150px; max-height: 150px;">
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" name="edit_submit" class="btn btn-primary">ยืนยันการแก้ไข</button>
                        <a href="view_assets.php" class="btn btn-secondary">ยกเลิก</a>
                    </div>
                <?php endif; ?>
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