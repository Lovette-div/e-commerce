<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/settings/core.php';
require_once dirname(__DIR__, 2) . '/settings/db_class.php';
require_once dirname(__DIR__, 2) . '/controllers/brand_controller.php';

$response = [];

try {
    // Check if user logged in
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception('User not logged in');
    }

    // Decode JSON payload from fetch()
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['brand_id'])) {
        throw new Exception('Brand ID is required');
    }

    $brand_id = intval($data['brand_id']);
    $user_id  = $_SESSION['customer_id'];

    // Instantiate controller
    $ctrl = new BrandController();
    $result = $ctrl->delete_brand_ctr($brand_id, $user_id);

    if (isset($result['success']) && $result['success'] === true) {
        $response = ['status' => true, 'message' => $result['msg'] ?? 'Brand deleted successfully'];
    } else {
        throw new Exception($result['msg'] ?? 'Failed to delete brand');
    }

} catch (Throwable $e) {
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
