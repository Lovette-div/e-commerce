<?php
ob_start();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Clear any previous output
ob_clean();

session_start();

$response = array();

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['status'] = false;
        $response['message'] = 'Method not allowed. Only POST requests are accepted.';
        echo json_encode($response);
        exit();
    }

    // If already logged in, block access
    if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
        $response['status'] = false;
        $response['message'] = 'You are already logged in';
        echo json_encode($response);
        exit();
    }

    // Debug: Log that we reached this point
    error_log("Register action: Starting registration process");

    //check if file exists
    $controllerPath = __DIR__ . '/../controllers/customer_controller.php';
    if (!file_exists($controllerPath)) {
        $response['status'] = false;
        $response['message'] = 'Controller file not found';
        echo json_encode($response);
        exit();
    }

    require_once $controllerPath;

    // Handle JSON request body
    $input = json_decode(file_get_contents("php://input"), true);

    // Debug: Log the input
    error_log("Register action: Input received: " . print_r($input, true));

    // Validate JSON input
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = false;
        $response['message'] = 'Invalid JSON data: ' . json_last_error_msg();
        echo json_encode($response);
        exit();
    }

    // Fallback if not JSON (form submit)
    if (!$input) {
        $input = $_POST;
        error_log("Register action: Using POST data as fallback");
    }

    // Extract and validate input
    $name = isset($input['name']) ? trim($input['name']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $contact = isset($input['contact']) ? trim($input['contact']) : '';
    $country = isset($input['country']) ? trim($input['country']) : '';
    $city = isset($input['city']) ? trim($input['city']) : '';
    $userRole = isset($input['user_role']) ? intval($input['user_role']) : 2;

    // Debug: Log extracted values
    error_log("Register action: Extracted values - Name: $name, Email: $email, Country: $country, City: $city, Contact: $contact");

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($contact) || empty($country) || empty($city)) {
        $response['status'] = false;
        $response['message'] = 'All fields are required';
        echo json_encode($response);
        exit();
    }

    // Check if CustomerController class exists
    if (!class_exists('CustomerController')) {
        $response['status'] = false;
        $response['message'] = 'CustomerController class not found';
        echo json_encode($response);
        exit();
    }

    // Create customer controller instance
    $customerController = new CustomerController();

    // Prepare registration data
    $customerData = array(
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'contact' => $contact,
        'country' => $country,
        'city' => $city,
        'user_role' => $userRole
    );

    error_log("Register action: Calling registerCustomerCtr method");

    // Call the registration method
    $result = $customerController->registerCustomerCtr($customerData);

    error_log("Register action: Registration result: " . print_r($result, true));

    if ($result['status']) {
        $response['status'] = true;
        $response['message'] = $result['message'];
        $response['customer_id'] = isset($result['customer_id']) ? $result['customer_id'] : null;
        $response['redirect'] = 'login.php';
        
        error_log("Register action: Successful registration for email: " . $email);
    } else {
        $response['status'] = false;
        $response['message'] = $result['message'];
        
        error_log("Register action: Failed registration for email: " . $email . " - " . $result['message']);
    }

} catch (Exception $e) {
    error_log("Register action: Exception - " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $response['status'] = false;
    $response['message'] = 'Server error: ' . $e->getMessage();
} catch (Error $e) {
    error_log("Register action: Fatal error - " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    $response['status'] = false;
    $response['message'] = 'Fatal error: ' . $e->getMessage();
}

// Ensure we only output JSON
ob_clean();
echo json_encode($response);
exit();
?>