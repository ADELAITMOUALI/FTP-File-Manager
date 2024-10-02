<?php
session_start();
require_once 'ftp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true || !isset($_POST['name'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated or name not provided']);
    exit;
}

$ftpConnection = ftpConnect($_SESSION['user'], $_SESSION['password']);

if (!$ftpConnection) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to FTP server']);
    exit;
}

$name = $_POST['name'];
$type = $_POST['type'];
$currentDir = $_POST['currentDir'] ?? '.';

try {
    if ($currentDir != '.') {
        if (!ftpChangeDirectory($ftpConnection, $currentDir)) {
            throw new Exception("Failed to change directory to: $currentDir");
        }
    }
    
    if ($type === 'dir') {
        $deleteResult = ftpRemoveDirectory($ftpConnection, $name);
    } else {
        $deleteResult = ftpDeleteFile($ftpConnection, $name);
    }
    
    if ($deleteResult) {
        echo json_encode(['success' => true]);
    } else {
        $errorMsg = ($type === 'dir') ? "Failed to delete folder: $name" : "Failed to delete file: $name";
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    ftpDisconnect($ftpConnection);
}
?>