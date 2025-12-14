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

if ($cart_id == 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid cart item']);
    exit;
}

$cartController = new CartController();
$result = $cartController->remove_from_cart_ctr($cart_id);

if ($result) {
    $cart_count = $cartController->get_cart_count_ctr($_SESSION['customer_id']);
    echo json_encode([
        'status' => true, 
        'message' => 'Item removed from cart',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to remove item']);
}
?>