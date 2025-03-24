<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('../config/config.php');
require_once('../models/database.php');

class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $image_file = K_PATH_IMAGES.'../assets/images/logo.png';
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 15, 'Asset Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // Subtitle
        $this->SetFont('helvetica', '', 12);
        $this->Ln(8);
        $this->Cell(0, 15, "Month: " . $_GET['month'] . ", Year: " . $_GET['year'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
}

$month = $_GET['month'];
$year = $_GET['year'];

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Asset Management System');
$pdf->SetTitle('Asset Report');
$pdf->SetSubject('Asset Report for ' . $month . '/' . $year);
$pdf->SetKeywords('TCPDF, PDF, asset, report');

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add a title
$pdf->Cell(0, 10, 'Asset Report', 0, 1, 'C');

// Add a table with headers
$html = '
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Asset Name</th>
            <th>Category</th>
            <th>Purchase Date</th>
            <th>Warranty Expiry Date</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update the SQL query to use the correct column names
$sql = "SELECT a.name, at.asset_type_name AS category, a.purchase_date, a.warranty_expiry_date 
        FROM assets a 
        JOIN asset_types at ON a.asset_type_id = at.assets_types_id 
        WHERE MONTH(a.purchase_date) = '$month' AND YEAR(a.purchase_date) = '$year'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>
            <td>' . $row['name'] . '</td>
            <td>' . $row['category'] . '</td>
            <td>' . $row['purchase_date'] . '</td>
            <td>' . $row['warranty_expiry_date'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="4">No records found</td></tr>';
}

$html .= '
    </tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('asset_report.pdf', 'I');

$conn->close();
?>
