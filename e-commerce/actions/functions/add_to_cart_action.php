<?php
header('Content-Type: application/json');

// Prevent any output before JSON
ob_clean();

require_once(dirname(__DIR__, 2).'/settings/core.php');
require_once(dirname(__DIR__, 2).'/controllers/cart_controller.php');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => 'Please login to add items to cart']);
    exit;
}

// Get POST data - handle both JSON and form data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If JSON decode fails, try $_POST
if ($data === null) {
    $data = $_POST;
}

$product_id = intval($data['product_id'] ?? 0);
$qty = intval($data['qty'] ?? 1);
$customer_id = $_SESSION['customer_id'];

// Validate input
if ($product_id == 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid product']);
    exit;
}

if ($qty <= 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid quantity']);
    exit;
}

try {
    // Add to cart
    $cartController = new CartController();
    $params = [
        'product_id' => $product_id,
        'customer_id' => $customer_id,
        'qty' => $qty
    ];

    $result = $cartController->add_to_cart_ctr($params);

    if ($result) {
        // Get updated cart count
        $cart_count = $cartController->get_cart_count_ctr($customer_id);
        
        echo json_encode([
            'status' => true, 
            'message' => 'Product added to cart successfully',
            'cart_count' => $cart_count
        ]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to add product to cart']);
    }
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'An error occurred. Please try again.']);
}
exit;
?>