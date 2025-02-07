<?php
// add_asset_type.php - หน้าเพิ่มประเภทสินทรัพย์
include_once '../includes/navbar.php';
include_once '../models/assetModel.php';

$assetModel = new AssetModel();

// ดึงข้อมูลประเภทสินทรัพย์ทั้งหมด
$assetTypes = $assetModel->fetchAssetTypes(true); // Include hidden types

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $assetTypeName = trim($_POST['name']);

    if (!empty($assetTypeName)) {
        try {
            if ($assetModel->addOrUpdateAssetType($assetTypeName)) {
                echo "<script>alert('เพิ่มประเภทสินทรัพย์สำเร็จ!'); window.location.href = 'view_assets.php';</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('เกิดข้อผิดพลาดในการเพิ่มประเภทสินทรัพย์!'); window.location.href = 'view_assets.php';</script>";
        }
    } else {
        echo "<script>alert('กรุณากรอกชื่อประเภทสินทรัพย์!');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hide_id'])) {
    $hideId = $_POST['hide_id'];

    try {
        if ($assetModel->hideAssetType($hideId)) {
            echo "<script>alert('ลบประเภทสินทรัพย์สำเร็จ!'); window.location.href = 'view_assets.php';</script>";
        } else {
            throw new Exception('เกิดข้อผิดพลาดในการลบประเภทสินทรัพย์!');
        }
    } catch (Exception $e) {
        echo "<script>alert('ไม่สามารถลบประเภทสินทรัพย์ได้เนื่องจากมีสินทรัพย์ที่เกี่ยวข้อง!'); window.location.href = 'view_assets.php';</script>";
    }
}
?>

<div class="container mt-5">
    <h1 class="text-center">จัดการประเภทสินทรัพย์</h1>

    <!-- ส่วนเพิ่มประเภท -->
    <div class="card mt-4">
        <div class="card-header">
            <h4>เพิ่มประเภทสินทรัพย์</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">ชื่อประเภทสินทรัพย์</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="กรอกชื่อประเภทสินทรัพย์" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h4>ลบประเภทสินทรัพย์</h4>
        </div>
        <div class="card-body">
            <ul class="list-group" id="assetTypeList">
                <?php foreach ($assetTypes as $type): ?>
                    <?php if ($type['asset_type_hidden'] == 0): ?> <!-- Change column name -->
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="type-<?php echo $type['assets_types_id']; ?>">
                            <?php echo htmlspecialchars($type['asset_type_name']); ?>
                            <span 
                                class="badge bg-danger drag-badge" 
                                draggable="true" 
                                ondragstart="drag(event)">
                                ลากไปที่ถังขยะ
                            </span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="mt-4 text-center">
        <div id="trashBin" class="btn btn-danger" ondrop="drop(event)" ondragover="allowDrop(event)">
            ถังขยะ
        </div>
    </div>
</div>

<script>
    function allowDrop(event) {
        event.preventDefault();
    }

    function drag(event) {
        event.dataTransfer.setData("text", event.target.parentElement.id);
    }

    function drop(event) {
        event.preventDefault();
        const id = event.dataTransfer.getData("text").replace('type-', '');
        if (confirm("คุณต้องการลบประเภทสินทรัพย์นี้ใช่หรือไม่?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'hide_id';
            input.value = id;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include_once '../includes/footer.php'; ?>
