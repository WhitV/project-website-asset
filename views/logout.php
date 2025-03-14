<?php
session_start();
include_once '../models/logger.php';

$logger = new Logger();
$logger->log('User logged out');

session_unset();
session_destroy();
header('Location: pin.php');
exit();
?>
