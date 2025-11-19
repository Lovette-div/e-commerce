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

    $customer_id = $_SESSION['customer_id'];
    $categories = fetch_categories_ctr($customer_id);

    if ($categories && count($categories) > 0) {
        $response = ['status' => true, 'categories' => $categories];
    } else {
        $response = ['status' => false, 'message' => 'No categories found'];
    }
} catch (Throwable $e) {
    // Return the PHP error to the browser
    $response = ['status' => false, 'message' => 'Server error: ' . $e->getMessage()];
}

echo json_encode($response);
