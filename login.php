<?php
session_start();
require_once 'ftp_functions.php';

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$conn_id = ftpConnect($username, $password);
if ($conn_id) {
    // Login successful, set session variables
    $_SESSION['user'] = $username;
    $_SESSION['password'] = $password;
    $_SESSION['authenticated'] = true;
    
    ftpDisconnect($conn_id);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}
?>