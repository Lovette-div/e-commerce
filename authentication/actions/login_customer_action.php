<?php
// Start session to manage user login state
session_start();

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("status" => false, "message" => "Method not allowed. Only POST requests are accepted."));
    exit();
}

// Include the customer controller
require_once(__DIR__ . '/../controllers/customer_controller.php');

try {
    // Get JSON input from request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If no JSON data received, try to get from POST (fallback)
    if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    
    // Check if required data is present
    if (empty($data)) {
        echo json_encode(array("status" => false, "message" => "No data received. Please provide login information."));
        exit();
    }
    
    // Create customer controller instance
    $customerController = new CustomerController();
    
    // Prepare login data array
    $loginData = array(
        'email' => isset($data['email']) ? $data['email'] : '',
        'password' => isset($data['password']) ? $data['password'] : ''
    );
    
    // Call the login customer controller method
    $result = $customerController->loginCustomerCtr($loginData);
    
    // If login is successful, set session variables
    if ($result['status'] && isset($result['customer'])) {
        $customer = $result['customer'];
        
        // Set session variables for user ID, role, name, and other attributes
        $_SESSION['customer_id'] = $customer['customer_id'];
        $_SESSION['customer_name'] = $customer['customer_name'];
        $_SESSION['customer_email'] = $customer['customer_email'];
        $_SESSION['customer_country'] = $customer['customer_country'];
        $_SESSION['customer_city'] = $customer['customer_city'];
        $_SESSION['customer_contact'] = $customer['customer_contact'];
        $_SESSION['customer_image'] = $customer['customer_image'];
        $_SESSION['user_role'] = $customer['user_role'];
        $_SESSION['login_time'] = time();
        $_SESSION['is_logged_in'] = true;
        
        // Set session timeout (1 hour)
        $_SESSION['expire_time'] = time() + 3600;
        
        // Add success message
        $result['message'] = "Login successful! Welcome back, " . $customer['customer_name'] . "!";
        
        // Set appropriate HTTP status code
        http_response_code(200); // Success
    } else {
        // Set appropriate HTTP status code for failed login
        http_response_code(401); // Unauthorized
    }
    
    // Return JSON response
    echo json_encode($result);
    
} catch (Exception $e) {
    // Handle exceptions
    http_response_code(500);
    echo json_encode(array(
        "status" => false, 
        "message" => "Server error: " . $e->getMessage()
    ));
} catch (Error $e) {
    // Handle fatal errors
    http_response_code(500);
    echo json_encode(array(
        "status" => false, 
        "message" => "Fatal error: " . $e->getMessage()
    ));
}
?>