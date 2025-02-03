<?php
// add_item.php
require_once 'config.php';
require_once 'functions.php';
session_start();
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to list items');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get all form data including quantity
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $sku = generateUniqueSku($_POST['sku']);
    $paypal_email = trim($_POST['paypal_email']);
    $quantity = intval($_POST['quantity']); // New quantity field
    $user_id = $_SESSION['user_id'];

    // Validation
    if (empty($title) || empty($sku) || empty($paypal_email)) {
        throw new Exception('Required fields missing');
    }

    if ($price <= 0) {
        throw new Exception('Price must be greater than 0');
    }

    // Validate quantity
    if ($quantity < 1) {
        throw new Exception('Quantity must be at least 1');
    }

    if (!filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid PayPal email format');
    }

    // Get the next available ID
    $id_query = "SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM items";
    $id_result = $conn->query($id_query);
    if (!$id_result) {
        throw new Exception('Error getting next ID: ' . $conn->error);
    }
    $next_id = $id_result->fetch_assoc()['next_id'];

    // Insert item with explicit ID and quantity
    $stmt = $conn->prepare("
        INSERT INTO items (
            id, user_id, title, description, 
            price, sku, paypal_email, quantity
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception('SQL Error: ' . $conn->error);
    }

    $stmt->bind_param(
        "iissdssi", 
        $next_id, $user_id, $title, $description, 
        $price, $sku, $paypal_email, $quantity
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Item listed successfully',
            'sku' => $sku,
            'id' => $next_id,
            'quantity' => $quantity
        ]);
    } else {
        throw new Exception('Error listing item: ' . $conn->error);
    }

} catch (Exception $e) {
    error_log("Add item error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}