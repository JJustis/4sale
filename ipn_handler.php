<?php
// Include the PayPal IPN class
require_once 'PaypalIPN.php';
require_once 'config.php';

// Set up logging
ini_set('log_errors', 1);
ini_set('error_log', 'ipn_errors.log');

function logIPN($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'ipn.log');
}

// Create instance of PaypalIPN
$paypal_ipn = new PaypalIPN();

// Read POST data

logIPN('Raw IPN: ' . $raw_post_data);

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}
if ($myPost['payment_status'] == 'Completed') {
    $purchase_details = array(
        'payer_email' => $myPost['payer_email'],
        'address_name' => $myPost['address_name'],
        'address_street' => $myPost['address_street'],
        'address_city' => $myPost['address_city'],
        'address_state' => $myPost['address_state'],
        'address_zip' => $myPost['address_zip'],
        'address_country' => $myPost['address_country'],
        'quantity' => $myPost['quantity']
    );
    
    sendPurchaseMessage($conn, $myPost['custom'], $purchase_details);
}
// Build verification string
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

logIPN('Verification String: ' . $req);

// Use the verifyIPN() method of PaypalIPN class to validate the IPN
try {
    if ($paypal_ipn->verifyIPN()) {
        logIPN('Payment VERIFIED');
        
        // Get transaction data
        $item_sku = isset($myPost['custom']) ? $myPost['custom'] : '';
        $payment_status = isset($myPost['payment_status']) ? $myPost['payment_status'] : '';
        $payment_amount = isset($myPost['mc_gross']) ? (float)$myPost['mc_gross'] : 0.0;
        $payment_currency = isset($myPost['mc_currency']) ? $myPost['mc_currency'] : '';
        $txn_id = isset($myPost['txn_id']) ? $myPost['txn_id'] : '';
        $receiver_email = isset($myPost['receiver_email']) ? $myPost['receiver_email'] : '';
        $payer_email = isset($myPost['payer_email']) ? $myPost['payer_email'] : '';
        $quantity = isset($myPost['quantity']) ? (int)$myPost['quantity'] : 1;

        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Check for duplicate transaction
            $dup_check = $conn->prepare("SELECT id FROM sales WHERE transaction_id = ?");
            $dup_check->bind_param("s", $txn_id);
            $dup_check->execute();
            if ($dup_check->get_result()->num_rows > 0) {
                throw new Exception("Duplicate transaction");
            }
            
            // Get item details
            $stmt = $conn->prepare("SELECT paypal_email, quantity FROM items WHERE sku = ? FOR UPDATE");
            $stmt->bind_param("s", $item_sku);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            
            if (!$item) {
                throw new Exception("Item not found");
            }
            
            // Verify quantity
            if ($item['quantity'] < $quantity) {
                throw new Exception("Insufficient quantity");
            }
            
            // Update quantity
            $new_quantity = $item['quantity'] - $quantity;
            $update_stmt = $conn->prepare("UPDATE items SET quantity = ? WHERE sku = ?");
            $update_stmt->bind_param("is", $new_quantity, $item_sku);
            $update_stmt->execute();
            
            // Record sale
            $sale_stmt = $conn->prepare("
                INSERT INTO sales (
                    sku,
                    transaction_id,
                    quantity,
                    buyer_email,
                    payment_status,
                    payment_amount,
                    payment_currency,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $sale_stmt->bind_param(
                "ssissds",
                $item_sku,
                $txn_id,
                $quantity,
                $payer_email,
                $payment_status,
                $payment_amount,
                $payment_currency
            );
            
            $sale_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            logIPN('Transaction completed successfully');
            
        } catch (Exception $e) {
            $conn->rollback();
            logIPN('Transaction failed: ' . $e->getMessage());
        }
        
    } else {
        logIPN('Invalid IPN verification');
    }
} catch (Exception $e) {
    logIPN('Verification failed: ' . $e->getMessage());
}
