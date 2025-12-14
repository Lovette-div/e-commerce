<?php
header('Content-Type: application/json');

require_once(dirname(__DIR__, 1).'/settings/core.php');
require_once(dirname(__DIR__, 1).'/controllers/cart_controller.php');
require_once(dirname(__DIR__, 1).'/controllers/order_controller.php');
require_once(dirname(__DIR__, 1).'/controllers/product_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $customer_id = $_SESSION['customer_id'];
    
    // Initialize controllers
    $cartController = new CartController();
    $orderController = new OrderController();
    
    // Step 1: Get cart items
    $cart_items = $cartController->get_user_cart_ctr($customer_id);
    
    if (empty($cart_items)) {
        echo json_encode(['status' => false, 'message' => 'Your cart is empty']);
        exit;
    }
    
    // Step 2-Calculate total amount
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['subtotal'];
    }
    
    // Step 3: Generate unique invoice number
    $invoice_no = $orderController->generate_invoice_number_ctr();
    
    // Step 4: Create order
    $order_params = [
        'customer_id' => $customer_id,
        'invoice_no' => $invoice_no,
        'order_amount' => $total_amount,
        'order_status' => 'Pending'
    ];
    
    $order_id = $orderController->create_order_ctr($order_params);
    
    if (!$order_id) {
        echo json_encode(['status' => false, 'message' => 'Failed to create order']);
        exit;
    }
    
    // Step 5: Add order details for each cart item
    $all_details_added = true;
    foreach ($cart_items as $item) {
        $detail_params = [
            'order_id' => $order_id,
            'product_id' => $item['p_id'],
            'qty' => $item['qty'],
            'price' => $item['price']
        ];
        
        $detail_result = $orderController->add_order_details_ctr($detail_params);
        if (!$detail_result) {
            $all_details_added = false;
            break;
        }
    }
    
    if (!$all_details_added) {
        echo json_encode(['status' => false, 'message' => 'Failed to add order details']);
        exit;
    }
    
    // Step 6: Record payment
    $payment_params = [
        'order_id' => $order_id,
        'amount' => $total_amount,
        'currency' => 'USD',
        'payment_method' => 'Simulated Payment'
    ];
    
    $payment_result = $orderController->record_payment_ctr($payment_params);
    
    if (!$payment_result) {
        echo json_encode(['status' => false, 'message' => 'Failed to record payment']);
        exit;
    }
    
    // Step 7: Update order status to 'Confirmed'
    $orderController->update_order_status_ctr($order_id, 'Confirmed');
    
    // Step 8: Empty the cart
    $cart_cleared = $cartController->empty_cart_ctr($customer_id);
    
    if (!$cart_cleared) {
        // Log this but don't fail the checkout
        error_log("Failed to clear cart for customer: $customer_id after order: $order_id");
    }
    
    // Step 9: Return success response
    echo json_encode([
        'status' => true,
        'message' => 'Order placed successfully!',
        'order_id' => $order_id,
        'invoice_no' => $invoice_no,
        'order_amount' => number_format($total_amount, 2),
        'order_date' => date('F j, Y, g:i a')
    ]);
    
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    echo json_encode([
        'status' => false, 
        'message' => 'An error occurred during checkout. Please try again.'
    ]);
}
?>