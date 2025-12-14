<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');
require_once('../controllers/category_controller.php');

$response = [];

try {
    if (!isset($_SESSION['customer_id'])) {
        throw new Exception('User not logged in');
    }

    if (empty($_POST['cat_id'])) {
        throw new Exception('Category ID is required');
    }

    $cat_id = intval($_POST['cat_id']);

    $result = delete_category_ctr($cat_id);

    if ($result) {
        $response = ['status' => true, 'message' => 'Category deleted successfully'];
    } else {
        throw new Exception('Failed to delete category');
    }

} catch (Throwable $e) {
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
?>
