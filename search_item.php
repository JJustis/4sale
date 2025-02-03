<?php
// search_item.php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $sku = trim($_POST['sku'] ?? '');
    
    if (empty($sku)) {
        throw new Exception('SKU is required');
    }

    // First check listings - now including image
    $listings_query = "SELECT l.*, u.username as seller_username 
                      FROM listings l 
                      JOIN users u ON l.seller_id = u.id 
                      WHERE l.sku = ?";

    $listings_stmt = $conn->prepare($listings_query);
    if ($listings_stmt === false) {
        throw new Exception('Prepare listings statement failed: ' . $conn->error);
    }

    $listings_stmt->bind_param("s", $sku);
    
    if (!$listings_stmt->execute()) {
        throw new Exception('Listings query execution failed: ' . $listings_stmt->error);
    }

    $listings_result = $listings_stmt->get_result();
    $listings_stmt->close();

    // Check items - now including image
    $items_query = "SELECT * FROM items WHERE sku = ?";
    $items_stmt = $conn->prepare($items_query);
    
    if ($items_stmt === false) {
        throw new Exception('Prepare items statement failed: ' . $conn->error);
    }

    $items_stmt->bind_param("s", $sku);
    
    if (!$items_stmt->execute()) {
        throw new Exception('Items query execution failed: ' . $items_stmt->error);
    }

    $items_result = $items_stmt->get_result();
    $items_stmt->close();

    // Combine results
    $results = [];

    // Add listings results with image
    while ($row = $listings_result->fetch_assoc()) {
        $results[] = [
            'type' => 'listing',
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],
            'paypal_email' => $row['paypal_email'],
            'seller_username' => $row['seller_username'],
            'image' => $row['image'] ?? '' // Add image field
        ];
    }

    // Add items results with image
    while ($row = $items_result->fetch_assoc()) {
        $results[] = [
            'type' => 'item',
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],
            'paypal_email' => $row['paypal_email'],
            'image' => $row['image'] ?? '' // Add image field
        ];
    }

    if (empty($results)) {
        throw new Exception('No items found with this SKU');
    }

    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}