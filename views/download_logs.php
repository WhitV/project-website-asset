<?php
session_start();

if (!isset($_SESSION['authenticated'])) {
    header('Location: ../views/pin.php');
    exit();
}

$logFilePath = '../logs/app.log';

if (file_exists($logFilePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="logs.txt"');
    readfile($logFilePath);
} else {
    echo "Log file not found.";
}
?>
