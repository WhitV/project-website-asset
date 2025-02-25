<?php
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: pin.php');
    exit();
}

// dashboard.php - หน้า Dashboard
include_once '../includes/navbar.php';
include_once '../models/assetModel.php';


// Function to fetch asset summary data
function fetchAssetSummary($assetModel) {
    try {
        return [
            'totalAssets' => $assetModel->getTotalAssets(),
            'retiredAssets' => $assetModel->getRetiredAssets(),
            'assetByType' => $assetModel->getAssetCountByType(),
            'totalPrice' => $assetModel->getTotalAssetPrice(),
            'oldestAsset' => $assetModel->getOldestAsset(),
            'newestAsset' => $assetModel->getNewestAsset(),
            'allAssets' => $assetModel->fetchAllAssets()
        ];
    } catch (Exception $e) {
        error_log("Error fetching asset data: " . $e->getMessage());
        return [
            'totalAssets' => 0,
            'retiredAssets' => 0,
            'assetByType' => [],
            'totalPrice' => 0,
            'oldestAsset' => null,
            'newestAsset' => null,
            'allAssets' => []
        ];
    }
}

// Fetch asset summary data
$assetModel = new AssetModel();
$assetSummary = fetchAssetSummary($assetModel);

// Function to generate asset summary cards
function generateSummaryCards($assetSummary) {
    ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mt-4">
        <div class="col">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">สินทรัพย์ทั้งหมด</h5>
                    <p class="card-text display-4 text-center"><?php echo $assetSummary['totalAssets']; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-danger h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title text-center">สินทรัพย์ที่ปลดแล้ว</h5>
                    <p class="card-text display-4 text-center"><?php echo $assetSummary['retiredAssets']; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-info h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">สินทรัพย์ใหม่ล่าสุด</h5>
                    <p class="card-text text-center">
                        <?php echo htmlspecialchars($assetSummary['newestAsset']['name'] ?? 'ไม่มีข้อมูล'); ?><br>
                        วันที่ซื้อ: <?php echo htmlspecialchars($assetSummary['newestAsset']['purchase_date'] ?? '-'); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-secondary h-100 text-center">
                <div class="card-body">
                    <h5 class="card-title">สินทรัพย์เก่าที่สุด</h5>
                    <p class="card-text text-center">
                        <?php echo htmlspecialchars($assetSummary['oldestAsset']['name'] ?? 'ไม่มีข้อมูล'); ?><br>
                        วันที่ซื้อ: <?php echo htmlspecialchars($assetSummary['oldestAsset']['purchase_date'] ?? '-'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Function to generate asset type cards
function generateAssetTypeCards($assetSummary) {
    $filteredAssetByType = array_filter($assetSummary['assetByType'], function ($type) {
        return !empty($type['name']);
    });

    usort($filteredAssetByType, function ($a, $b) {
        return $b['count'] - $a['count'];
    });

    $topAssetTypes = array_slice($filteredAssetByType, 0, 3);
    ?>
    <div class="row row-cols-1 row-cols-md-2 g-4 mt-4">
        <div class="col">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h5 class="card-title">ราคาสินทรัพย์ทั้งหมด</h5>
                    <p class="card-text display-4 text-center"><?php echo number_format($assetSummary['totalPrice'], 2); ?> บาท</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h5 class="card-title">ประเภทสินทรัพย์</h5>
                    <ul class="list-group">
                        <?php foreach ($topAssetTypes as $type): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($type['name']); ?>
                                <span class="badge bg-secondary"><?php echo $type['count']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Function to generate asset table
function generateAssetTable($allAssets) {
    ?>
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 style="margin-left: 10px;">ประวัติการจัดการสินทรัพย์</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">ลำดับ</th>
                                    <th style="text-align: center;">รหัสสินทรัพย์</th>
                                    <th style="text-align: center;">หมายเลขครุภัณฑ์</th>
                                    <th style="text-align: center;">ชื่อ</th>
                                    <th style="text-align: center;">ประเภท</th>
                                    <th style="text-align: center;">สถานที่</th>
                                    <th style="text-align: center;">แผนก</th>
                                    <th style="text-align: center;">วันที่ซื้อ</th>
                                    <th style="text-align: center;">ราคา (บาท)</th>
                                    <th style="text-align: center;">สถานะ</th>
                                    <th style="text-align: center;">รูปภาพ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allAssets)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">ไม่มีสินทรัพย์ในระบบ</td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $displayLimit = 20;
                                    $shownCount = 0;

                                    foreach ($allAssets as $index => $asset): 
                                        if ($shownCount >= $displayLimit) break; 
                                        $shownCount++;
                                    ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $index + 1; ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['asset_code']); ?></td> 
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['inventory_number'] ?? 'N/A'); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['name']); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['asset_type_name']); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['location']); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['department']); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['purchase_date']); ?></td>
                                            <td style="text-align: center;"><?php echo number_format($asset['price'], 2); ?></td>
                                            <td style="text-align: center;"><?php echo htmlspecialchars($asset['status']); ?></td>
                                            <td style="text-align: center;">
                                                <?php if (!empty($asset['image'])): ?>
                                                    <img src="../public/uploads/<?php echo htmlspecialchars($asset['image']); ?>" alt="Asset Image" style="width: 50px; height: 50px;">
                                                <?php else: ?>
                                                    ไม่มีรูปภาพ
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="./view_assets.php" class="btn btn-primary btn-lg w-100 btn-fixed-color">ดูรายการสินทรัพย์</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<!-- ห่อหุ้มเนื้อหาทั้งหมดด้วย container -->
<div class="container mt-5">
    <h1 class="text-center">Dashboard</h1>

    <!-- สรุปข้อมูลด้วย Cards -->
    <?php generateSummaryCards($assetSummary); ?>

    <!-- สรุปข้อมูลเพิ่มเติมด้วย Cards -->
    <?php generateAssetTypeCards($assetSummary); ?>

    <!-- ตารางสินทรัพย์ทั้งหมด -->
    <?php generateAssetTable($assetSummary['allAssets']); ?>
</div>

<?php include_once '../includes/footer.php'; ?>