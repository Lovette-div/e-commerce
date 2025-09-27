<?php
header('Content-Type: application/json');
session_start();

$response = array();

// If already logged in, block access
if (isset($_SESSION['user_id'])) {
    $response['status'] = false;
    $response['message'] = 'You are already logged in';
    echo json_encode($response);
    exit();
}

require_once '../controllers/customer_controller.php';

// Handle JSON request body
$input = json_decode(file_get_contents("php://input"), true);

// Fallback if not JSON (form submit)
if (!$input) {
    $input = $_POST;
}

$name         = isset($input['name']) ? trim($input['name']) : '';
$email        = isset($input['email']) ? trim($input['email']) : '';
$password     = isset($input['password']) ? $input['password'] : '';
$phone_number = isset($input['contact']) ? trim($input['contact']) : '';
$country      = isset($input['country']) ? trim($input['country']) : '';
$city         = isset($input['city']) ? trim($input['city']) : '';
$role         = isset($input['user_role']) ? intval($input['user_role']) : 2; // default customer role

// Extra: check if email already exists
if (email_exists_ctr($email)) { 
    $response['status'] = false;
    $response['message'] = 'This email is already registered';
    echo json_encode($response);
    exit();
}

// Register user
$user_id = register_user_ctr($name, $email, $password, $phone_number, $role, $country, $city);

if ($user_id) {
    $response['status'] = true;
    $response['message'] = 'Registered successfully';
    $response['user_id'] = $user_id;
    $response['redirect'] = 'login.php'; // 👈 Add redirect here
} else {
    $response['status'] = false;
    $response['message'] = 'Failed to register. Try again later.';
}

echo json_encode($response);
