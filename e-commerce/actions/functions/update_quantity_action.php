<?php
header('Content-Type: application/json');

require_once(dirname(__DIR__, 2).'/settings/core.php');
require_once(dirname(__DIR__, 2).'/controllers/cart_controller.php');

if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$cart_id = intval($data['cart_id'] ?? 0);
$qty = intval($data['qty'] ?? 0);

if ($cart_id == 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid cart item']);
    exit;
}

if ($qty < 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid quantity']);
    exit;
}

$cartController = new CartController();
$result = $cartController->update_cart_item_ctr($cart_id, $qty);

if ($result) {
    $cart_total = $cartController->get_cart_total_ctr($_SESSION['customer_id']);
    $cart_count = $cartController->get_cart_count_ctr($_SESSION['customer_id']);
    
    echo json_encode([
        'status' => true, 
        'message' => 'Quantity updated',
        'cart_total' => number_format($cart_total, 2),
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to update quantity']);
}
?>