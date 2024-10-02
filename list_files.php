<?php
session_start();
require_once __DIR__ . '/ftp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$ftpConnection = ftpConnect($_SESSION['user'], $_SESSION['password']);

error_log('FTP Connection: ' . ($ftpConnection ? 'Success' : 'Failure'));
if (!$ftpConnection) {
    error_log('FTP Connection Error: ' . error_get_last()['message']);
    echo json_encode(['error' => 'Failed to connect to FTP server']);
    exit;
}

$currentDir = $_GET['dir'] ?? '.';
error_log('Current Directory: ' . $currentDir);

try {
    if ($currentDir != '.') {
        ftpChangeDirectory($ftpConnection, $currentDir);
    }
    $files = ftpListFiles($ftpConnection);
    error_log('Files: ' . print_r($files, true));
    echo json_encode($files);
} catch (Exception $e) {
    error_log('Error listing files: ' . $e->getMessage());
    echo json_encode(['error' => 'Failed to list files: ' . $e->getMessage()]);
} finally {
    ftpDisconnect($ftpConnection);
}
?>