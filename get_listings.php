<?php
// get_listings.php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $query = "SELECT l.*, u.username as seller_username 
              FROM listings l 
              JOIN users u ON l.seller_id = u.id 
              ORDER BY l.created_at DESC";
              
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception($conn->error);
    }
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'seller_username' => $row['seller_username'],
            'paypal_email' => $row['paypal_email']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $items,
            'pagination' => [
                'total' => count($items),
                'page' => 1,
                'per_page' => count($items)
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching listings',
        'debug_message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>