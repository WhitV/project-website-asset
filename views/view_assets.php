<?php
/**********************************************************
 * 1. ส่วน Import/Include
 **********************************************************/
include_once '../includes/navbar.php';
include_once '../models/database.php';

/**********************************************************
 * 2. เชื่อมต่อฐานข้อมูล & ประกาศตัวแปรที่ใช้ทำงาน
 **********************************************************/
$db = new Database();
$conn = $db->connection;

// กำหนดจำนวนรายการต่อหน้า
$limit = 50;

// รับหมายเลขหน้าจาก URL (ถ้าไม่มีให้ตั้งเป็นหน้าแรก)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/**********************************************************
 * 3. ฟังก์ชันใช้งานในระบบ (CRUD หรือ Query ต่าง ๆ)
 **********************************************************/
function fetchData($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAssets($conn, $limit, $offset) {
    $query = "
        SELECT assets.*, asset_types.asset_type_name AS asset_type_name 
        FROM assets 
        LEFT JOIN asset_types ON assets.asset_type_id = asset_types.assets_types_id 
        WHERE assets.status = 'Active'
        ORDER BY assets.id DESC 
        LIMIT :limit OFFSET :offset
    ";
    return fetchData($conn, $query, [':limit' => $limit, ':offset' => $offset]);
}

function fetchTotalAssets($conn) {
    $query = "SELECT COUNT(*) AS total FROM assets WHERE status = 'Active'";
    return fetchData($conn, $query)[0]['total'];
}

function fetchAssetTypes($conn) {
    $query = "SELECT assets_types_id, asset_type_name FROM asset_types WHERE asset_type_hidden = 0";
    return fetchData($conn, $query);
}

function fetchDepartments($conn) {
    $query = "
        SELECT DISTINCT department 
        FROM assets 
        WHERE asset_type_id IN (
            SELECT assets_types_id FROM asset_types WHERE asset_type_hidden = 0
        ) AND status = 'Active'
    ";
    return fetchData($conn, $query);
}

// 3.5 สร้างตัวเลือก (Filter) ประเภทและแผนก
function generateFilterOptions($types, $departments) {
    ?>
        <div class="myHeader">
            <h1>รายการสินทรัพย์</h1>
        </div>

        <section class="myForm">
            <ul class="myform-menu">
                <li><a href="./add_asset.php" class="btn btn-primary">เพิ่มสินทรัพย์</a></li>
                <li><a href="./add_asset_type.php" class="btn btn-primary">เพิ่ม/ลบประเภทสินทรัพย์</a></li>
            </ul>

            <!-- ฟิลเตอร์ประเภท -->
            <div class="inputbox">
                <select id="typeFilter" class="form-select">
                    <option value="">ประเภท</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['asset_type_name']); ?>">
                            <?php echo htmlspecialchars($type['asset_type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        
            <!-- ฟิลเตอร์แผนก -->
            <div class="inputbox">
                <select id="departmentFilter" class="form-select">
                    <option value="">แผนก</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department['department']); ?>">
                            <?php echo htmlspecialchars($department['department']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </section>
    <?php
}

// 3.6 สร้างตารางสินทรัพย์
function generateAssetTable($assets, $offset) {
    ?>
    <div class="table-responsive">
        <table class="table table-striped text-center">
            <thead>
                <tr>
                    <th style="pointer-events: none; text-align: center;">ลำดับ</th>
                    <th style="text-align: center;">รหัสสินทรัพย์</th>
                    <th style="text-align: center;">เลขครุภัณฑ์</th>
                    <th style="text-align: center;">ชื่อสินทรัพย์</th>
                    <th style="text-align: center;">ประเภท</th>
                    <th style="text-align: center;">Serial Number</th>
                    <th style="text-align: center;">สถานที่</th>
                    <th style="text-align: center;">แผนก</th>
                    <th style="text-align: center;">วันที่ซื้อ</th>
                    <th style="text-align: center;">ราคา (บาท)</th>
                    <th style="text-align: center;">ผู้รับผิดชอบ</th>
                    <th style="pointer-events: none; text-align: center">รูปภาพ</th>
                    <th style="pointer-events: none; text-align: center">การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assets as $index => $asset): ?>
                    <tr>
                        <!-- # ลำดับ -->
                        <td style="text-align: center;">
                            <?php echo $offset + $index + 1; ?>
                        </td>                                     
                        <!-- รหัสสินทรัพย์ -->
                        <td 
                            class="barcode-cell" 
                            style="text-decoration: underline; cursor: pointer;"
                            onmouseover="this.style.color='blue';"
                            onmouseout="this.style.color='';">
                            <?php echo htmlspecialchars($asset['asset_code']); ?>
                        </td>
                        <!-- เลขครุภัณฑ์ -->
                        <td 
                            class="barcode-cell" 
                            style="text-decoration: underline; cursor: pointer;"
                            onmouseover="this.style.color='blue';"
                            onmouseout="this.style.color='';">
                            <?php echo htmlspecialchars($asset['inventory_number']); ?>
                        </td>
                        <!-- ชื่อสินทรัพย์ -->
                        <td><?php echo htmlspecialchars($asset['name']); ?></td>
                        <!-- ประเภท -->
                        <td><?php echo htmlspecialchars($asset['asset_type_name']); ?></td>
                        <!-- Serial Number -->
                        <td><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                        <!-- สถานที่ -->
                        <td><?php echo htmlspecialchars($asset['location']); ?></td>
                        <!-- แผนก -->
                        <td><?php echo htmlspecialchars($asset['department']); ?></td>
                        <!-- วันที่ซื้อ -->
                        <td><?php echo htmlspecialchars($asset['purchase_date']); ?></td>
                        <!-- ราคา -->
                        <td><?php echo number_format($asset['price'], 2); ?></td>
                        <!-- ผู้รับผิดชอบ -->
                        <td><?php echo htmlspecialchars($asset['responsible_person']); ?></td>
                        <!-- รูปภาพ -->
                        <td>
                            <?php if (!empty($asset['image'])): ?>
                                <img 
                                    src="../public/uploads/<?php echo htmlspecialchars($asset['image']); ?>"
                                    alt="Asset Image"
                                    class="img-fluid"
                                    style="max-width: 50px; max-height: 50px;">
                            <?php else: ?>
                                ไม่มีรูปภาพ
                            <?php endif; ?>
                        </td>
                        <!-- ปุ่มจัดการ (แก้ไข / Retire) -->
                        <td>
                            <div class="d-flex justify-content-center">
                                <a href="edit_asset.php?id=<?php echo $asset['id']; ?>"
                                   class="btn btn-warning btn-sm me-2">
                                    แก้ไข
                                </a>
                                <button 
                                    class="btn btn-danger btn-sm confirmRetireButton"
                                    data-bs-toggle="modal"
                                    data-bs-target="#retireModal<?php echo $asset['id']; ?>"
                                    data-asset-id="<?php echo $asset['id']; ?>">
                                    ปลด
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Modal ยืนยันการปลดสินทรัพย์ -->
                    <div class="modal fade"
                         id="retireModal<?php echo $asset['id']; ?>"
                         tabindex="-1"
                         aria-labelledby="retireModalLabel<?php echo $asset['id']; ?>"
                         aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="color: rgb(15, 19, 24);">
                                    <h5 class="modal-title" id="retireModalLabel<?php echo $asset['id']; ?>">
                                        ยืนยันการปลดสินทรัพย์
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="color: rgb(15, 19, 24);">
                                    <p>
                                        คุณแน่ใจหรือไม่ว่าต้องการเปลี่ยนสถานะสินทรัพย์
                                        "<strong><?php echo htmlspecialchars($asset['name']); ?></strong>"
                                        เป็นเลิกใช้งาน?
                                    </p>
                                    <div class="mb-3">
                                        <label for="confirmAssetName<?php echo $asset['id']; ?>" class="form-label">
                                            กรุณากรอกชื่อสินทรัพย์เพื่อยืนยัน:
                                        </label>
                                        <input type="text" class="form-control" id="confirmAssetName<?php echo $asset['id']; ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        ยกเลิก
                                    </button>
                                    <button type="button"
                                            class="btn btn-danger confirmRetireConfirm"
                                            data-asset-id="<?php echo $asset['id']; ?>"
                                            data-asset-name="<?php echo htmlspecialchars($asset['name']); ?>">
                                        ยืนยัน
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**********************************************************
 * 4. เรียกใช้ฟังก์ชันเพื่อดึงข้อมูลจาก Database
 **********************************************************/
$assets       = fetchAssets($conn, $limit, $offset);
$totalAssets  = fetchTotalAssets($conn);
$types        = fetchAssetTypes($conn);
$departments  = fetchDepartments($conn);
?>

<!-- ***********************************************
     5. ส่วน Style เฉพาะเพิ่มเติม (Inline CSS)
************************************************ -->
<style>
    th.sortable {
        cursor: pointer;
        user-select: none; /* ป้องกันการเลือกข้อความขณะคลิก */
    }
    th.sorted-asc::after {
        content: "▲";
    }
    th.sorted-desc::after {
        content: "▼";
    }
    .barcode-cell {
        color: blue;
        text-decoration: underline;
        cursor: pointer;
    }
    .barcode-cell:hover {
        text-decoration: none;
    }
</style>

<!-- ***********************************************
     6. ส่วนโครงสร้าง HTML แสดงผลหน้าเว็บ
************************************************ -->

<div class="container mt-5">
    <div class="mt-4">
        <?php generateFilterOptions($types, $departments); ?>
        <div class="col-md-auto">
            <form autocomplete="off">
                <div class="input-group">
                    <input 
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="ค้นหาด้วยรหัสสินทรัพย์, ชื่อสินทรัพย์, Serial Number หรือสถานที่"
                        autocomplete="off">
                    <button class="btn btn-primary" style="pointer-events: none; opacity: 1;">ค้นหา</button>
                </div>
            </form>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <p id="totalAssets" style="margin-left: 10px;">
                        จำนวนสินทรัพย์ทั้งหมด: <?php echo $totalAssets; ?>
                    </p>
                </div>
                <div class="card-body">
                    <?php 
                        generateAssetTable($assets, $offset);
                        $totalPages = ceil($totalAssets / $limit);
                    ?>
                    <div class="d-flex justify-content-end">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>"
                               class="btn btn-primary btn-sm me-1 <?php echo ($i === $page) ? 'active' : ''; ?>"
                               <?php echo ($i === $page) ? 'aria-current="page"' : ''; ?>>
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- ปิด container -->

<?php include_once '../includes/footer.php'; ?>

<!-- ***********************************************
     7. ส่วน Script (JavaScript) สำหรับ Event/Function
************************************************ -->

<!-- (7.1) สคริปต์สำหรับค้นหาและกรอง -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput        = document.getElementById('searchInput');
    const typeFilter         = document.getElementById('typeFilter');
    const departmentFilter   = document.getElementById('departmentFilter');
    const tableRows          = document.querySelectorAll('table tbody tr');
    const totalAssetsElement = document.getElementById('totalAssets');

    // ฟังก์ชันกรองข้อมูลในตาราง
    function filterTable() {
        const searchValue       = searchInput.value.trim().toLowerCase();
        const selectedType      = typeFilter.value.toLowerCase();
        const selectedDepartment= departmentFilter.value.toLowerCase();

        let visibleIndex = 1;    // ตัวนับสำหรับลำดับใหม่
        let filteredCount = 0;   // ตัวนับจำนวนสินทรัพย์ที่ตรงเงื่อนไข

        tableRows.forEach(row => {
            const assetCode       = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const inventoryNumber = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const assetName       = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const assetType       = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            const serialNumber    = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
            const location        = row.querySelector('td:nth-child(7)').textContent.toLowerCase();
            const department      = row.querySelector('td:nth-child(8)').textContent.toLowerCase();

            // ตรวจสอบเงื่อนไขการค้นหา
            const matchesSearch = (
                assetCode.includes(searchValue) ||
                inventoryNumber.includes(searchValue) ||
                assetName.includes(searchValue) ||
                serialNumber.includes(searchValue) ||
                location.includes(searchValue)
            );
            const matchesType       = (selectedType === '' || assetType === selectedType);
            const matchesDepartment = (selectedDepartment === '' || department === selectedDepartment);

            if (matchesSearch && matchesType && matchesDepartment) {
                row.style.display = '';
                filteredCount++;

                // ปรับลำดับใหม่
                const orderCell = row.querySelector('td:nth-child(1)');
                if (orderCell) {
                    orderCell.textContent = visibleIndex++;
                }
            } else {
                row.style.display = 'none';
            }
        });

        // อัปเดตจำนวนสินทรัพย์
        totalAssetsElement.innerHTML = `จำนวนสินทรัพย์ทั้งหมด: ${filteredCount}`;
    }

    // Event Listener
    searchInput.addEventListener('input', filterTable);
    typeFilter.addEventListener('change', filterTable);
    departmentFilter.addEventListener('change', filterTable);
});
</script>

<!-- (7.2) สคริปต์สำหรับการจัดเรียงคอลัมน์ (Sort) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const table   = document.querySelector('table');
    const headers = table.querySelectorAll('th');
    const rows    = Array.from(table.querySelectorAll('tbody tr'));

    headers.forEach((header, index) => {
        header.classList.add('sortable');
        header.addEventListener('click', () => {
            const isNumberColumn      = (index === 9); // ราคา (บาท)
            const isDateColumn        = (index === 8); // วันที่ซื้อ
            const isStatusColumn      = (index === 10); // สถานะ
            const isSerialNumberColumn= (index === 5);  // Serial Number
            const direction = (header.dataset.sortDirection === 'asc') ? 'desc' : 'asc';
            header.dataset.sortDirection = direction;

            rows.sort((a, b) => {
                const aText = a.children[index].textContent.trim();
                const bText = b.children[index].textContent.trim();

                // จัดเรียงตามประเภทคอลัมน์
                if (isNumberColumn) {
                    // คอลัมน์ราคา
                    return (direction === 'asc')
                        ? parseFloat(aText.replace(/,/g, '')) - parseFloat(bText.replace(/,/g, ''))
                        : parseFloat(bText.replace(/,/g, '')) - parseFloat(aText.replace(/,/g, ''));
                }
                if (isDateColumn) {
                    // คอลัมน์วันที่
                    return (direction === 'asc')
                        ? new Date(aText) - new Date(bText)
                        : new Date(bText) - new Date(aText);
                }
                if (isStatusColumn) {
                    // คอลัมน์สถานะ (Active ก่อน Retired)
                    const statusOrder = { Active: 1, Retired: 2 };
                    return (direction === 'asc')
                        ? statusOrder[aText] - statusOrder[bText]
                        : statusOrder[bText] - statusOrder[aText];
                }
                if (isSerialNumberColumn) {
                    // คอลัมน์ Serial Number แบบ localeCompare
                    return (direction === 'asc')
                        ? aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' })
                        : bText.localeCompare(aText, undefined, { numeric: true, sensitivity: 'base' });
                }

                // คอลัมน์ตัวอักษรทั่วไป
                return (direction === 'asc')
                    ? aText.localeCompare(bText, undefined, { numeric: true, sensitivity: 'base' })
                    : bText.localeCompare(aText, undefined, { numeric: true, sensitivity: 'base' });
            });

            // อัปเดตแถวในตาราง
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));

            // อัปเดตลำดับในคอลัมน์ # ให้รันตั้งแต่ 1 ใหม่
            tbody.querySelectorAll('tr').forEach((row, i) => {
                row.querySelector('td:nth-child(1)').textContent = i + 1;
            });

            // จัดการคลาสแสดงลูกศรขึ้น/ลง
            headers.forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
            header.classList.add(direction === 'asc' ? 'sorted-asc' : 'sorted-desc');
        });
    });
});
</script>

<!-- (7.3) สคริปต์สำหรับยืนยันการ Retire สินทรัพย์ -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.confirmRetireConfirm').forEach(button => {
        button.addEventListener('click', () => {
            const assetId   = button.getAttribute('data-asset-id');
            const assetName = button.getAttribute('data-asset-name').toLowerCase().trim();
            const inputName = document.getElementById(`confirmAssetName${assetId}`).value.toLowerCase().trim();

            // ตรวจสอบชื่อสินทรัพย์
            if (inputName === assetName) {
                fetch('../views/retire_asset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: assetId })
                })
                .then(response => {
                    if (response.ok) {
                        alert('เปลี่ยนสถานะสำเร็จ');
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด กรุณาลองอีกครั้ง');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                });
            } else {
                alert('ชื่อสินทรัพย์ไม่ตรงกับฐานข้อมูล กรุณากรอกใหม่อีกครั้ง');
            }
        });
    });
});
</script>

<!-- (7.4) สคริปต์สำหรับการสร้างบาร์โค้ด -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('td:nth-child(2), td:nth-child(3)').forEach(cell => {
        cell.style.cursor = 'pointer';
        cell.addEventListener('click', () => {
            const value = cell.textContent.trim();
            const modal = new bootstrap.Modal(document.getElementById('barcodeModal'));
            document.getElementById('barcodeValue').value = value;
            modal.show();
        });
    });

    document.getElementById('barcodeModalConfirm').addEventListener('click', () => {
        const value  = document.getElementById('barcodeValue').value;
        const format = document.getElementById('barcodeFormat').value;
        window.open(`generate_barcode.php?value=${encodeURIComponent(value)}&format=${format}`);
        const modal = bootstrap.Modal.getInstance(document.getElementById('barcodeModal'));
        modal.hide();
    });
});
</script>

<!-- (7.5) โมดัลสำหรับเลือกรูปแบบไฟล์ (PNG, JPG, PDF) -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style ="color: rgb(15, 19, 24);">
                <h5 class="modal-title" id="barcodeModalLabel">เลือกประเภทไฟล์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style ="color: rgb(15, 19, 24);">
                <form id="barcodeForm">
                    <div class="mb-3">
                        <label for="barcodeFormat" class="form-label">ประเภทไฟล์</label>
                        <select class="form-select" id="barcodeFormat" required>
                            <option value="png">PNG</option>
                            <option value="jpg">JPG</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <input type="hidden" id="barcodeValue">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="barcodeModalConfirm">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
