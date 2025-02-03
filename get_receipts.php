<?php
// get_receipts.php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            s.*, i.title, i.price
        FROM sales s
        JOIN items i ON s.sku = i.sku
        WHERE s.buyer_email = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $receipts = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(['success' => true, 'receipts' => $receipts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}