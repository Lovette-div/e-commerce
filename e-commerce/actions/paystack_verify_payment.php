<?php
/**
 * Paystack Payment Verification Action
 * Called via AJAX from payment_callback.php
 * Verifies payment with Paystack API, creates order, records payment, and clears cart
 */

session_start();
header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';
require_once '../controllers/order_controller.php';
require_once '../classes/cart_class.php';
require_once '../settings/db_class.php';

// Enable error logging
error_log("=== PAYSTACK VERIFY PAYMENT ACTION ===");

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;

error_log("Payment reference: $reference");

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment reference is required'
    ]);
    exit();
}

// Get customer ID from session
$customer_id = $_SESSION['customer_id'];
error_log("Customer ID: $customer_id");

try {
    // Step 1: Check if order already exists (prevent duplicate orders)
    $existingOrder = checkExistingOrder($reference);
    if ($existingOrder) {
        error_log("Order already exists for reference: $reference");
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Order already processed',
            'invoice_no' => $reference,
            'duplicate' => true
        ]);
        exit();
    }

    // Step 2: Get cart items before verification
    $cartClass = new Cart();
    $cartItems = $cartClass->viewCart($customer_id);
    
    error_log("Cart items count: " . count($cartItems));

    if (empty($cartItems)) {
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => 'Your cart is empty'
        ]);
        exit();
    }

    // Step 3: Calculate total amount from cart
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $totalAmount += ($item['product_price'] * $item['qty']);
    }
    
    error_log("Total amount from cart: GH₵" . $totalAmount);

    // Step 4: Verify payment with Paystack
    $verification = verifyPaystackPayment($reference);
    
    error_log("Paystack verification status: " . ($verification['status'] ? 'true' : 'false'));

    if (!$verification['status']) {
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => $verification['message'] ?? 'Payment verification failed'
        ]);
        exit();
    }

    $paymentData = $verification['data'];
    error_log("Payment status from Paystack: " . $paymentData['status']);

    // Step 5: Check if payment was successful
    if ($paymentData['status'] !== 'success') {
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => 'Payment was not successful. Status: ' . $paymentData['status']
        ]);
        exit();
    }

    // Step 6: Verify payment amount matches cart total (in kobo for Paystack)
    $paidAmount = $paymentData['amount'] / 100; // Convert from kobo to cedis
    
    error_log("Amount paid: GH₵$paidAmount | Amount expected: GH₵$totalAmount");

    // Allow small difference due to floating point
    if (abs($paidAmount - $totalAmount) > 0.01) {
        error_log("AMOUNT MISMATCH! Paid: $paidAmount, Expected: $totalAmount");
        echo json_encode([
            'status' => 'error',
            'verified' => false,
            'message' => 'Payment amount mismatch. Please contact support.'
        ]);
        exit();
    }

    // Extract payment details
    $customerEmail = $paymentData['customer']['email'] ?? '';
    $authorization = $paymentData['authorization'] ?? [];
    $authorizationCode = $authorization['authorization_code'] ?? '';
    $paymentMethod = $authorization['channel'] ?? 'card';
    $cardLastFour = $authorization['last4'] ?? '';

    // Step 7: Begin database transaction
    $db = new db_connection();
    $conn = $db->db_conn();
    mysqli_begin_transaction($conn);
    
    error_log("Database transaction started");

    try {
        // Generate invoice number
        $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        $order_date = date('Y-m-d H:i:s');
        
        error_log("Generated invoice: $invoice_no");

        // Create the order
        $orderController = new OrderController();
        $order_id = $orderController->createOrderWithTransaction($customer_id, $invoice_no, 'Paid');

        if (!$order_id) {
            throw new Exception("Failed to create order");
        }

        error_log("Order created with ID: $order_id");

        // Add order details for each cart item
        foreach ($cartItems as $item) {
            $detailAdded = $orderController->addOrderDetailWithTransaction(
                $order_id,
                $item['product_id'],
                $item['qty']
            );
            
            if (!$detailAdded) {
                throw new Exception("Failed to add order details for product: {$item['product_id']}");
            }
            
            error_log("Added order detail - Product: {$item['product_id']}, Qty: {$item['qty']}");
        }

        // Step 8: Record payment in payment table
        $paymentRecorded = recordPayment(
            $conn,
            $totalAmount,
            $customer_id,
            $order_id,
            'GHS',
            $order_date,
            'paystack',
            $reference,
            $authorizationCode,
            $paymentMethod
        );

        if (!$paymentRecorded) {
            throw new Exception("Failed to record payment");
        }

        error_log("Payment recorded - Reference: $reference");

        // Step 9: Clear customer cart
        $cartCleared = $orderController->clearCartWithTransaction($customer_id);

        if (!$cartCleared) {
            throw new Exception("Failed to clear cart");
        }

        error_log("Cart cleared for customer: $customer_id");

        // Commit transaction
        mysqli_commit($conn);
        error_log("Database transaction committed successfully");

        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);

        // Step 10: Success - Return response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'order_created' => true,
            'message' => 'Payment verified and order created successfully',
            'invoice_no' => $invoice_no,
            'order_id' => $order_id,
            'amount' => number_format($totalAmount, 2),
            'currency' => 'GHS',
            'payment_reference' => $reference,
            'payment_method' => ucfirst($paymentMethod),
            'order_date' => date('F j, Y', strtotime($order_date)),
            'item_count' => count($cartItems)
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        error_log("Database transaction rolled back: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("ERROR in payment verification: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'An error occurred while processing your payment: ' . $e->getMessage()
    ]);
}

/**
 * Verify payment with Paystack API
 * @param string $reference Payment reference
 * @return array Verification result
 */
function verifyPaystackPayment($reference) {
    global $paystack_secret_key;
    
    // Use secret key from config
    $secret_key = $paystack_secret_key ?? 'sk_test_b72beb25e5781d72f95f420c75d1a05e3e173fb0';
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $secret_key,
            "Cache-Control: no-cache",
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        error_log("Paystack cURL error: " . $err);
        return [
            'status' => false,
            'message' => 'Connection error: ' . $err
        ];
    }
    
    $result = json_decode($response, true);
    
    error_log("Paystack API response: " . print_r($result, true));
    
    if (!$result || !isset($result['status'])) {
        error_log("Invalid Paystack response: " . $response);
        return [
            'status' => false,
            'message' => 'Invalid response from payment gateway'
        ];
    }
    
    return $result;
}

/**
 * Check if order already exists with this invoice number
 * Prevents duplicate orders on page refresh
 * @param string $invoice_no Invoice number
 * @return bool True if order exists
 */
function checkExistingOrder($invoice_no) {
    require_once '../settings/db_class.php';
    
    $db = new db_connection();
    $conn = $db->db_conn();
    
    $sql = "SELECT order_id FROM orders WHERE invoice_no = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Database error in checkExistingOrder: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("s", $invoice_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exists = $result->num_rows > 0;
    $stmt->close();
    
    return $exists;
}

/**
 * Record payment in payment table
 * @param mysqli $conn Database connection
 * @param float $amount Payment amount
 * @param int $customer_id Customer ID
 * @param int $order_id Order ID
 * @param string $currency Currency code
 * @param string $payment_date Payment date
 * @param string $payment_method Payment method
 * @param string $reference Payment reference
 * @param string $authorization_code Authorization code
 * @param string $channel Payment channel
 * @return bool Success status
 */
function recordPayment($conn, $amount, $customer_id, $order_id, $currency, $payment_date, $payment_method, $reference, $authorization_code, $channel) {
    $sql = "INSERT INTO payment (
                amt, 
                customer_id, 
                order_id, 
                currency, 
                payment_date, 
                payment_method,
                payment_reference,
                authorization_code,
                payment_channel
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        error_log("Payment insert prepare failed: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param(
        "diisssss",
        $amount,
        $customer_id,
        $order_id,
        $currency,
        $payment_date,
        $payment_method,
        $reference,
        $authorization_code,
        $channel
    );
    
    $result = $stmt->execute();
    
    if (!$result) {
        error_log("Payment insert execute failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $payment_id = $stmt->insert_id;
    $stmt->close();
    
    error_log("Payment inserted with ID: $payment_id");
    return true;
}
?>