<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/ftp_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$ftpConnection = ftpConnect($_SESSION['user'], $_SESSION['password']);

if (!$ftpConnection) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to FTP server']);
    exit;
}

$file = $_FILES['file'];
$currentDir = $_POST['currentDir'] ?? '.';

try {
    if ($currentDir != '.') {
        ftpChangeDirectory($ftpConnection, $currentDir);
    }
    $uploadResult = ftpUploadFile($ftpConnection, $file['tmp_name'], $file['name']);

    if ($uploadResult) {
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error uploading file: ' . $e->getMessage()]);
} finally {
    ftpDisconnect($ftpConnection);
}
?>