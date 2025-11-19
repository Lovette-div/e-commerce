<?php
require_once(__DIR__ . '/../classes/customer_class.php');

class CustomerController {
    private $customer;
    
    public function __construct() {
        $this->customer = new Customer();
    }
    
    //main method for registration
    public function registerCustomerCtr($customerData) {
        //validation
        $requiredFields = ['name', 'email', 'password', 'country', 'city', 'contact'];
        
        foreach ($requiredFields as $field) {
            if (!isset($customerData[$field]) || empty(trim($customerData[$field]))) {
                return array("status" => false, "message" => "All fields are required!");
            }
        }
        
        // Sanitize input data
        $name = trim($customerData['name']);
        $email = trim(strtolower($customerData['email']));
        $password = $customerData['password'];
        $country = trim($customerData['country']);
        $city = trim($customerData['city']);
        $contact = trim($customerData['contact']);
        $userRole = isset($customerData['user_role']) ? $customerData['user_role'] : 2; // Default to customer (2)
        
        // Validate field lengths based on database schema
        if (strlen($name) > 100) {
            return array("status" => false, "message" => "Name must be less than 100 characters!");
        }
        
        if (strlen($email) > 50) {
            return array("status" => false, "message" => "Email must be less than 50 characters!");
        }
        
        if (strlen($country) > 30) {
            return array("status" => false, "message" => "Country must be less than 30 characters!");
        }
        
        if (strlen($city) > 30) {
            return array("status" => false, "message" => "City must be less than 30 characters!");
        }
        
        if (strlen($contact) > 15) {
            return array("status" => false, "message" => "Contact number must be less than 15 characters!");
        }
        
        // Additional validation
        if (!$this->validateEmail($email)) {
            return array("status" => false, "message" => "Invalid email format!");
        }
        
        if (!$this->validatePassword($password)) {
            return array("status" => false, "message" => "Password must be at least 8 characters long with at least one uppercase letter, one lowercase letter, and one number!");
        }
        
        if (!$this->validateContact($contact)) {
            return array("status" => false, "message" => "Invalid contact number format!");
        }
        
        if (!$this->validateName($name)) {
            return array("status" => false, "message" => "Name must contain only letters and spaces!");
        }
        
        // Call the customer model to add customer
        return $this->customer->addCustomer($name, $email, $password, $country, $city, $contact, $userRole);
    }
    
    // Login customer controller
    public function loginCustomerCtr($loginData) {
        // Debug logging
        error_log("CustomerController: Login attempt for email: " . (isset($loginData['email']) ? $loginData['email'] : 'NOT SET'));
        
        // Validate required fields
        $requiredFields = ['email', 'password'];
        
        foreach ($requiredFields as $field) {
            if (!isset($loginData[$field]) || empty(trim($loginData[$field]))) {
                error_log("CustomerController: Missing required field: " . $field);
                return array("status" => false, "message" => "Email and password are required!");
            }
        }
        
        // Sanitize input data
        $email = trim(strtolower($loginData['email']));
        $password = $loginData['password'];
        
        // Validate email format
        if (!$this->validateEmail($email)) {
            error_log("CustomerController: Invalid email format: " . $email);
            return array("status" => false, "message" => "Invalid email format!");
        }
        
        // Call the customer model to authenticate user
        $result = $this->customer->loginCustomer($email, $password);
        
        // Debug logging
        if ($result['status']) {
            $userRole = isset($result['customer']['user_role']) ? $result['customer']['user_role'] : 'NOT SET';
            error_log("CustomerController: Login successful for user: " . $email . " with role: " . $userRole);
        } else {
            error_log("CustomerController: Login failed for user: " . $email . " - " . $result['message']);
        }
        
        return $result;
    }
    
    // Get customer by ID controller
    public function getCustomerCtr($customerId) {
        if (empty($customerId) || !is_numeric($customerId)) {
            return array("status" => false, "message" => "Invalid customer ID!");
        }
        
        // First, get all customers and find the one with matching ID
        $allCustomersResult = $this->customer->getAllCustomers();
        
        if (!$allCustomersResult['status']) {
            return array("status" => false, "message" => "Failed to retrieve customers!");
        }
        
        foreach ($allCustomersResult['customers'] as $customer) {
            if ($customer['customer_id'] == $customerId) {
                // Remove password if it exists
                unset($customer['customer_pass']);
                return array("status" => true, "customer" => $customer);
            }
        }
        
        return array("status" => false, "message" => "Customer not found!");
    }
    
    // Update customer controller
    public function editCustomerCtr($customerData) {
        $requiredFields = ['customer_id', 'name', 'email', 'country', 'city', 'contact'];
        
        foreach ($requiredFields as $field) {
            if (!isset($customerData[$field]) || empty(trim($customerData[$field]))) {
                return array("status" => false, "message" => "All fields are required!");
            }
        }
        
        $customerId = $customerData['customer_id'];
        $name = trim($customerData['name']);
        $email = trim(strtolower($customerData['email']));
        $country = trim($customerData['country']);
        $city = trim($customerData['city']);
        $contact = trim($customerData['contact']);
        
        // Validation
        if (!$this->validateEmail($email)) {
            return array("status" => false, "message" => "Invalid email format!");
        }
        
        if (!$this->validateContact($contact)) {
            return array("status" => false, "message" => "Invalid contact number format!");
        }
        
        if (!$this->validateName($name)) {
            return array("status" => false, "message" => "Name must contain only letters and spaces!");
        }
        
    
        return array("status" => false, "message" => "Edit customer functionality not yet implemented!");
        // return $this->customer->editCustomer($customerId, $name, $email, $country, $city, $contact);
    }
    
    // Delete customer controller 
    public function deleteCustomerCtr($customerId) {
        if (empty($customerId) || !is_numeric($customerId)) {
            return array("status" => false, "message" => "Invalid customer ID!");
        }
        
        return array("status" => false, "message" => "Delete customer functionality not yet implemented!");
        // return $this->customer->deleteCustomer($customerId);
    }
    
    // Check if email exists controller
    public function checkEmailExistsCtr($email) {
        $email = trim(strtolower($email));
        
        if (!$this->validateEmail($email)) {
            return array("status" => false, "message" => "Invalid email format!");
        }
        
        $exists = $this->customer->emailExists($email);
        return array("status" => true, "exists" => $exists);
    }
    
    // Get all customers controller
    public function getAllCustomersCtr() {
        return $this->customer->getAllCustomers();
    }
    
    // Validation methods
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function validatePassword($password) {
        // At least 8 characters, one uppercase, one lowercase, one number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    private function validateContact($contact) {
        // Allow various international phone number formats (digits, plus sign, hyphens, spaces)
        return preg_match('/^[\+]?[0-9\s\-\(\)]{7,15}$/', $contact);
    }
    
    private function validateName($name) {
        // Allow letters, spaces, hyphens, and apostrophes
        return preg_match('/^[a-zA-Z\s\-\'\.]{2,100}$/', $name);
    }
}
?>