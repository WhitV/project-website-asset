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
        $this->Cell(0, 15, 'Warranty Alerts', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // Subtitle
        $this->SetFont('helvetica', '', 12);
        $this->Ln(8);
        $this->Cell(0, 15, "All Assets Nearing Warranty Expiry", 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Asset Management System');
$pdf->SetTitle('Warranty Alerts');
$pdf->SetSubject('All Assets Nearing Warranty Expiry');
$pdf->SetKeywords('TCPDF, PDF, warranty, alerts');

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
$pdf->Cell(0, 10, 'Warranty Alerts', 0, 1, 'C');

// Add a table with headers
$html = '
<table border="1" cellpadding="4">
    <thead>
        <tr>
            <th>Asset Name</th>
            <th>Category</th>
            <th>Warranty Expiry Date</th>
        </tr>
    </thead>
    <tbody>';

// Fetch data from the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update the SQL query to fetch all assets nearing warranty expiry within one month
$sql = "SELECT a.name, at.asset_type_name AS category, a.warranty_expiry_date 
        FROM assets a 
        JOIN asset_types at ON a.asset_type_id = at.assets_types_id 
        WHERE a.warranty_expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>
            <td>' . $row['name'] . '</td>
            <td>' . $row['category'] . '</td>
            <td>' . $row['warranty_expiry_date'] . '</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="3">No records found</td></tr>';
}

$html .= '
    </tbody>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('warranty_alerts.pdf', 'I');

$conn->close();
?>
