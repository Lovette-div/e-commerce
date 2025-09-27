<?php
// Start session to manage user login state
session_start();
header('Content-Type: application/json');

//allow post requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Method not allowed. Only POST requests are accepted."]);
    exit();
}

//the customer controller
require_once(__DIR__ . '/../controllers/customer_controller.php');

try {
    //getting SON input from AJAX request
    $input = json_decode(file_get_contents('php://input'), true);
    
    // if json not available
    if (!$input) {
        $input = $_POST;
    }

    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => false, "message" => "Email and Password are required."]);
        exit();
    }

    // Create customer controller instance
    $customerController = new CustomerController();

    // Prepare login data
    $loginData = array(
        'email' => $email,
        'password' => $password
    );

    // Call the login method
    $result = $customerController->loginCustomerCtr($loginData);

    if ($result['status'] && isset($result['customer'])) {
        $customer = $result['customer'];

        // Set session variables
        $_SESSION['customer_id'] = $customer['customer_id'];
        $_SESSION['customer_name'] = $customer['customer_name'];
        $_SESSION['customer_email'] = $customer['customer_email'];
        $_SESSION['user_role'] = $customer['user_role'];
        $_SESSION['is_logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['expire_time'] = time() + 3600; // 1 hour timeout

        //url based on role
        $redirectUrl = '';
        if ($customer['user_role'] == 2 || $customer['user_role'] === 'admin') { // Admin role
            $redirectUrl = '../admin/category.php';
        } else { // Regular customer
            $redirectUrl = '../customer/dashboard.php'; 
        }

        //show success response with redirect URL
        echo json_encode([
            "status" => true, 
            "message" => "Login successful!",
            "redirect" => $redirectUrl,
            "role" => $customer['user_role']
        ]);
        exit();
    } else {
        // Invalid login
        echo json_encode(["status" => false, "message" => $result['message'] ?? "Invalid email or password."]);
        exit();
    }

} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => "Server error: " . $e->getMessage()]);
    exit();
} catch (Error $e) {
    echo json_encode(["status" => false, "message" => "Fatal error: " . $e->getMessage()]);
    exit();
}
?>