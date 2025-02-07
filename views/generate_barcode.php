<?php
require_once '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorJPG;
use Dompdf\Dompdf;
use Dompdf\Options;

// Get input values
$value = isset($_GET['value']) ? trim($_GET['value']) : '';
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'png';

if (empty($value)) {
    http_response_code(400);
    echo 'Error: Barcode value is required.';
    exit;
}

// Function to generate PNG barcode
function generatePngBarcode($value) {
    $generator = new BarcodeGeneratorPNG();
    return $generator->getBarcode($value, $generator::TYPE_CODE_128);
}

// Function to generate JPG barcode
function generateJpgBarcode($value) {
    $generator = new BarcodeGeneratorJPG();
    return $generator->getBarcode($value, $generator::TYPE_CODE_128);
}

// Function to generate PDF barcode
function generatePdfBarcode($value) {
    $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($value, $generator::TYPE_CODE_128);

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $html = '<div style="text-align: center;">
                <img src="data:image/png;base64,' . base64_encode($barcode) . '">
                <div>' . htmlspecialchars($value) . '</div>
             </div>';
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
}

try {
    if ($format === 'png') {
        $barcode = generatePngBarcode($value);

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="barcode.png"');
        echo $barcode;
    } elseif ($format === 'jpg') {
        $barcode = generateJpgBarcode($value);

        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="barcode.jpg"');
        echo $barcode;
    } elseif ($format === 'pdf') {
        $barcode = generatePdfBarcode($value);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="barcode.pdf"');
        echo $barcode;
    } else {
        http_response_code(400);
        echo 'Error: Unsupported format.';
    }
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error generating barcode: ' . $e->getMessage();
}
