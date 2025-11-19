<?php
require_once(__DIR__ . '/../db/db_class.php');

class Customer extends Database {
    
    public function __construct() {
        parent::__construct();
    }
    
    // Add new customer
    public function addCustomer($name, $email, $password, $country, $city, $contact, $userRole = 2) {
        try {
            if ($this->emailExists($email)) {
                return ["status" => false, "message" => "Email already exists!"];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO customer 
                (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image, user_role) 
                VALUES (?, ?, ?, ?, ?, ?, NULL, ?)";
            
            $stmt = $this->executeQuery($query, [$name, $email, $hashedPassword, $country, $city, $contact, $userRole]);

            if ($stmt->affected_rows > 0) {
                return ["status" => true, "message" => "Customer registered successfully!", "customer_id" => $stmt->insert_id];
            } else {
                return ["status" => false, "message" => "Failed to register customer!"];
            }
        } catch (Exception $e) {
            return ["status" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT customer_id FROM customer WHERE customer_email = ?";
        $stmt = $this->executeQuery($query, [$email]);
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Login customer
    public function loginCustomer($email, $password) {
        $query = "SELECT * FROM customer WHERE customer_email = ?";
        $stmt = $this->executeQuery($query, [$email]);
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            error_log("Customer class: No user found with email: " . $email);
            return ["status" => false, "message" => "Invalid email or password!"];
        }

        $customer = $result->fetch_assoc();
        
        // Debug log
        error_log("Customer class: Found user with role: " . $customer['user_role'] . " for email: " . $email);

        if (password_verify($password, $customer['customer_pass'])) {
            unset($customer['customer_pass']); 
            return ["status" => true, "message" => "Login successful!", "customer" => $customer];
        }

        error_log("Customer class: Password verification failed for email: " . $email);
        return ["status" => false, "message" => "Invalid email or password!"];
    }

    // Get customer by ID - MISSING METHOD
    public function getCustomerById($customerId) {
        try {
            $query = "SELECT * FROM customer WHERE customer_id = ?";
            $stmt = $this->executeQuery($query, [$customerId]);
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting customer by ID: " . $e->getMessage());
            return null;
        }
    }

    // Edit customer - MISSING METHOD
    public function editCustomer($customerId, $name, $email, $country, $city, $contact) {
        try {
            // Check if email exists for other customers
            $query = "SELECT customer_id FROM customer WHERE customer_email = ? AND customer_id != ?";
            $stmt = $this->executeQuery($query, [$email, $customerId]);
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ["status" => false, "message" => "Email already exists for another customer!"];
            }

            $query = "UPDATE customer SET 
                customer_name = ?, 
                customer_email = ?, 
                customer_country = ?, 
                customer_city = ?, 
                customer_contact = ? 
                WHERE customer_id = ?";
            
            $stmt = $this->executeQuery($query, [$name, $email, $country, $city, $contact, $customerId]);

            if ($stmt->affected_rows > 0) {
                return ["status" => true, "message" => "Customer updated successfully!"];
            } else {
                return ["status" => false, "message" => "No changes made or customer not found!"];
            }
        } catch (Exception $e) {
            return ["status" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    // Delete customer - MISSING METHOD
    public function deleteCustomer($customerId) {
        try {
            $query = "DELETE FROM customer WHERE customer_id = ?";
            $stmt = $this->executeQuery($query, [$customerId]);

            if ($stmt->affected_rows > 0) {
                return ["status" => true, "message" => "Customer deleted successfully!"];
            } else {
                return ["status" => false, "message" => "Customer not found!"];
            }
        } catch (Exception $e) {
            return ["status" => false, "message" => "Error: " . $e->getMessage()];
        }
    }

    // Get all customers
    public function getAllCustomers() {
        try {
            $query = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, customer_image, user_role FROM customer";
            $stmt = $this->executeQuery($query);
            $result = $stmt->get_result();

            $customers = [];
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }

            return ["status" => true, "customers" => $customers];
        } catch (Exception $e) {
            return ["status" => false, "message" => "Error: " . $e->getMessage()];
        }
    }
}
?>