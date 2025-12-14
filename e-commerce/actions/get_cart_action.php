<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$corePath = __DIR__ . '/../settings/core.php';
if (file_exists($corePath)) {
    require_once $corePath;
}

try {
    // Determine customer id
    if (function_exists('get_user_id')) {
        $customer_id = get_user_id();
    } else {
        $customer_id = isset($_SESSION['customer_id']) ? (int) $_SESSION['customer_id'] : null;
    }

    if (empty($customer_id)) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        exit;
    }

    // Load controller
    $cartControllerPath = __DIR__ . '/../controllers/cart_controller.php';
    if (!file_exists($cartControllerPath)) {
        throw new \RuntimeException('Cart controller not found');
    }
    require_once $cartControllerPath;

    $cartController = new CartController();
    $items = $cartController->get_user_cart_ctr($customer_id);

    if (!$items || count($items) === 0) {
        echo json_encode([
            'status' => 'success',
            'items' => [],
            'total' => 0.00,
            'count' => 0
        ]);
        exit;
    }

    // Normalize item fields and compute numeric subtotals safely
    $total = 0.0;
    foreach ($items as &$it) {
        $it['price']    = isset($it['price']) ? (float) $it['price'] : 0.0;
        $it['qty']      = isset($it['qty']) ? (int) $it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 0);
        $it['subtotal'] = isset($it['subtotal'])
            ? (float) $it['subtotal']
            : ($it['price'] * $it['qty']);
        //ensure title/key fields exist for frontend
        if (!isset($it['title']) && isset($it['p_name'])) {
            $it['title'] = $it['p_name'];
        }
        $total += $it['subtotal'];
    }
    unset($it);

    echo json_encode([
        'status' => 'success',
        'items'  => $items,
        'total'  => round($total, 2),
        'count'  => count($items)
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    error_log('get_cart_action error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Server error fetching cart']);
}
?>
