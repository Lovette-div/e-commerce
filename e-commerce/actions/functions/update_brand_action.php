<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/controllers/brand_controller.php';
require_once dirname(__DIR__, 2) . '/settings/core.php';
require_once dirname(__DIR__, 2) . '/settings/db_class.php';

$response = [];

try {
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception('User not logged in');
    }

    // Read JSON input from fetch()
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['brand_id']) || empty($data['brand_name']) || empty($data['cat_id'])) {
        throw new Exception('Missing brand ID, name, or category');
    }

    $brand_id   = intval($data['brand_id']);
    $brand_name = trim($data['brand_name']);
    $cat_id     = intval($data['cat_id']);
    $user_id    = $_SESSION['customer_id'];

    // Instantiate controller and calling the update method
    $ctrl = new BrandController();
    $result = $ctrl->update_brand_ctr($brand_id, $user_id, $brand_name, $cat_id);

    if (isset($result['success']) && $result['success'] === true) {
        $response = ['status' => true, 'message' => $result['msg'] ?? 'Brand updated successfully'];
    } else {
        $response = ['status' => false, 'message' => $result['msg'] ?? 'Failed to update brand'];
    }

} catch (Throwable $e) {
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
