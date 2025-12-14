<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/settings/core.php';
require_once dirname(__DIR__, 2) . '/settings/db_class.php';
require_once dirname(__DIR__, 2) . '/controllers/product_controller.php';

try {
    // Check login
    if (!isset($_SESSION['customer_id'])) {
        echo json_encode(['status' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $user_id = intval($_SESSION['customer_id']);
    $role = intval($_SESSION['user_role'] ?? 0);

    $ctrl = new ProductController();

    // Admins see all products, others see their own
    if ($role === 1) {
        $products = $ctrl->fetch_all_ctr();
    } else {
        $products = $ctrl->fetch_by_user_ctr($user_id);
    }

    if (!empty($products)) {
        echo json_encode(['status' => true, 'products' => $products]);
    } else {
        echo json_encode(['status' => false, 'message' => 'No products found']);
    }
} catch (Throwable $e) {
    echo json_encode(['status' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
