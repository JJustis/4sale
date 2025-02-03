<?php
session_start();
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
// send_message.php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$to_user = $_POST['to_user'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($to_user) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_SESSION['username'], $to_user, $message);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
