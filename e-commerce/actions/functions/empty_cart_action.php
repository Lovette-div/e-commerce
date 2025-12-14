<?php
header('Content-Type: application/json');

require_once(dirname(__DIR__, 2).'/settings/core.php');
require_once(dirname(__DIR__, 2).'/controllers/cart_controller.php');

if (!isLoggedIn()) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$cartController = new CartController();
$result = $cartController->empty_cart_ctr($_SESSION['customer_id']);

if ($result) {
    echo json_encode([
        'status' => true, 
        'message' => 'Cart emptied successfully',
        'cart_count' => 0
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'Failed to empty cart']);
}
?>