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

    if (empty($_POST['cat_name'])) {
        throw new Exception('Category name is required');
    }

    $cat_name = trim($_POST['cat_name']);
    $customer_id = $_SESSION['customer_id'];

    $result = add_category_ctr($cat_name, $customer_id);

    if ($result) {
        $response = ['status' => true, 'message' => 'Category added successfully'];
    } else {
        throw new Exception('Failed to add category');
    }

} catch (Throwable $e) {
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
?>
