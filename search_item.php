<?php
// search_item.php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
require_once 'config.php';
header('Content-Type: application/json');

try {
    error_log("POST data: " . print_r($_POST, true));
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $sku = trim($_POST['sku'] ?? '');
    
    if (empty($sku)) {
        throw new Exception('SKU is required');
    }

    error_log("Searching for SKU: " . $sku);

    // First check listings - now including quantity
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

    // Then check items - now including quantity
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

    // Add listings results with quantity
    while ($row = $listings_result->fetch_assoc()) {
        $results[] = [
            'type' => 'listing',
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],  // Added quantity field
            'paypal_email' => $row['paypal_email'],
            'seller_username' => $row['seller_username']
        ];
    }

    // Add items results with quantity
    while ($row = $items_result->fetch_assoc()) {
        $results[] = [
            'type' => 'item',
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'quantity' => $row['quantity'],  // Added quantity field
            'paypal_email' => $row['paypal_email']
        ];
    }

    if (empty($results)) {
        throw new Exception('No items found with this SKU');
    }
    error_log("Results data: " . print_r($results, true));
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}