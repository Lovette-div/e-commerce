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

    if (empty($_POST['cat_id']) || empty($_POST['cat_name'])) {
        throw new Exception('Category ID and name are required');
    }

    $cat_id = intval($_POST['cat_id']);
    $cat_name = trim($_POST['cat_name']);

    $result = update_category_ctr($cat_id, $cat_name);

    if ($result) {
        $response = ['status' => true, 'message' => 'Category updated successfully'];
    } else {
        throw new Exception('Failed to update category');
    }

} catch (Throwable $e) {
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
?>
