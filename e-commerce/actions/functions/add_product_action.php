<?php
error_reporting(E_ALL); ini_set('display_errors',1);
header('Content-Type: application/json');

require_once dirname(__DIR__,2).'/settings/core.php';
require_once dirname(__DIR__,2).'/controllers/product_controller.php';

try {
    if (!isset($_SESSION['customer_id'])) throw new Exception('Not logged in');
    // optionally allow non-admin sellers by role check
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
        // if you want only admin: throw
        // throw new Exception('Unauthorized');
        // else allow
    }

    $user_id = $_SESSION['customer_id'];
    // read POST (FormData)
    $title = trim($_POST['title'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $keyword = trim($_POST['keyword'] ?? '');
    $cat_id = intval($_POST['cat_id'] ?? 0);
    $brand_id = intval($_POST['brand_id'] ?? 0);

    if (!$title || !$cat_id || !$brand_id) throw new Exception('Missing required fields');

    $ctrl = new ProductController();
    $res = $ctrl->add_product_ctr($user_id, $title, $price, $description, $keyword, $cat_id, $brand_id);

    echo json_encode($res);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'msg'=>'Server error: '.$e->getMessage()]);
}
