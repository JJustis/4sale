<?php
session_start();
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// get_messages.php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE to_user = ? OR from_user = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}