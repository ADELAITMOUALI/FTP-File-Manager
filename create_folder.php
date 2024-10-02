<?php
session_start();
require_once __DIR__ . '/ftp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_POST['folderName'])) {
    echo json_encode(['success' => false, 'message' => 'Folder name not provided']);
    exit;
}

$ftpConnection = ftpConnect($_SESSION['user'], $_SESSION['password']);

if (!$ftpConnection) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to FTP server']);
    exit;
}

$folderName = $_POST['folderName'];
$currentDir = $_POST['currentDir'] ?? '.';

try {
    if ($currentDir != '.') {
        ftpChangeDirectory($ftpConnection, $currentDir);
    }
    $result = ftpCreateFolder($ftpConnection, $folderName);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Folder created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error creating folder: ' . $e->getMessage()]);
} finally {
    ftpDisconnect($ftpConnection);
}
?>