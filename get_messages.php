<?php
session_start();
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    // Fetch messages for the current user, with most recent first
    // Improved query to handle system and user messages
    $stmt = $conn->prepare("
        SELECT 
            id, 
            from_user, 
            to_user, 
            message, 
            created_at, 
            CASE 
                WHEN from_user = 'System' THEN 1 
                ELSE 0 
            END as is_system_message
        FROM messages 
        WHERE 
            to_user = ? OR 
            from_user = ? OR
            (from_user = 'System' AND to_user = ?)
        ORDER BY 
            is_system_message DESC, 
            created_at DESC 
        LIMIT 100
    ");
    $stmt->bind_param("sss", 
        $_SESSION['username'], 
        $_SESSION['username'],
        $_SESSION['username']
    );
    $stmt->execute();
    
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'messages' => $messages
    ]);
} catch (Exception $e) {
    error_log("Message retrieval error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error retrieving messages'
    ]);
}