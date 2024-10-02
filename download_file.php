<?php
session_start();
require_once 'ftp_functions.php';

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || !isset($_GET['filename'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$ftpConnection = ftpConnect($_SESSION['user'], $_SESSION['password']);

if (!$ftpConnection) {
    header('HTTP/1.1 500 Internal Server Error');
    exit;
}

$filename = $_GET['filename'];
$tempFile = tempnam(sys_get_temp_dir(), 'ftp_');

if (ftpDownloadFile($ftpConnection, $filename, $tempFile)) {
    ftpDisconnect($ftpConnection);
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    
    readfile($tempFile);
    unlink($tempFile);
} else {
    ftpDisconnect($ftpConnection);
    header('HTTP/1.1 404 Not Found');
}
?>