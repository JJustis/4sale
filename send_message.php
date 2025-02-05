<?php
session_start();
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

function sendResponse($success, $message = null) {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    echo json_encode($response);
    exit;
}

function sendPurchaseMessage($conn, $item_sku, $purchase_details) {
    // Find the seller of the item
    $stmt = $conn->prepare("SELECT user_id, title FROM items WHERE sku = ?");
    $stmt->bind_param("s", $item_sku);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("No item found for SKU: $item_sku");
        return false;
    }
    
    $item = $result->fetch_assoc();
    $seller_id = $item['user_id'];
    $item_title = $item['title'];
    
    // Get seller's username
    $username_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $username_stmt->bind_param("i", $seller_id);
    $username_stmt->execute();
    $username_result = $username_stmt->get_result();
    $seller = $username_result->fetch_assoc();
    
    // Prepare message with purchase details including shipping address
    $message = "New Purchase for '{$item_title}':\n";
    $message .= "Quantity: {$purchase_details['quantity']}\n";
    $message .= "Buyer Email: {$purchase_details['payer_email']}\n";
    $message .= "Shipping Address (to be used):\n";
    $message .= "{$purchase_details['address_name']}\n";
    $message .= "{$purchase_details['address_street']}\n";
    $message .= "{$purchase_details['address_city']}, {$purchase_details['address_state']} {$purchase_details['address_zip']}\n";
    $message .= "{$purchase_details['address_country']}";
    
    // Insert message to seller
    $insert_stmt = $conn->prepare("
        INSERT INTO messages (from_user, to_user, message, created_at) 
        VALUES ('System', ?, ?, NOW())
    ");
    $insert_stmt->bind_param("ss", $seller['username'], $message);
    
    // Insert address to sales record 
    $address_stmt = $conn->prepare("
        UPDATE sales 
        SET shipping_name = ?, 
            shipping_street = ?, 
            shipping_city = ?, 
            shipping_state = ?, 
            shipping_zip = ?, 
            shipping_country = ? 
        WHERE sku = ? AND buyer_email = ?
    ");
    $address_stmt->bind_param(
        "ssssssss", 
        $purchase_details['address_name'],
        $purchase_details['address_street'],
        $purchase_details['address_city'],
        $purchase_details['address_state'],
        $purchase_details['address_zip'],
        $purchase_details['address_country'],
        $item_sku,
        $purchase_details['payer_email']
    );
    
    // Execute both insert operations
    return $insert_stmt->execute() && $address_stmt->execute();
}

// Validate user authentication
if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Authentication required');
}

// Normal message sending
if (isset($_POST['to_user']) && isset($_POST['message'])) {
    $to_user = filter_input(INPUT_POST, 'to_user', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (empty($to_user) || empty($message)) {
        sendResponse(false, 'Missing or invalid message details');
    }

    try {
        $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $_SESSION['username'], $to_user, $message);
        $stmt->execute();
        
        sendResponse(true);
    } catch (Exception $e) {
        error_log("Message send error: " . $e->getMessage());
        sendResponse(false, 'Failed to send message');
    }
}