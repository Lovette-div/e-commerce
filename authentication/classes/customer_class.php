<?php
require_once(__DIR__ . '/../db/db_class.php');

class Customer extends Database {
    
    public function __construct() {
        parent::__construct();
    }
    
    // Add new customer
    public function addCustomer($name, $email, $password, $country, $city, $contact, $userRole = 2) {
        try {
            // Check if email already exists
            if ($this->emailExists($email)) {
                return array("status" => false, "message" => "Email already exists!");
            }
            
            // Hash the password using PHP's password_hash function
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO customer (customer_name, customer_email, customer_pass, customer_country, customer_city, customer_contact, customer_image, user_role) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)";
            
            $stmt = $this->executeQuery($query, [$name, $email, $hashedPassword, $country, $city, $contact, $userRole]);
            
            if ($stmt->affected_rows > 0) {
                $customerId = $this->connection->insert_id;
                return array("status" => true, "message" => "Customer registered successfully!", "customer_id" => $customerId);
            } else {
                return array("status" => false, "message" => "Failed to register customer!");
            }
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Error: " . $e->getMessage());
        }
    }
    
    // Check if email exists
    public function emailExists($email) {
        try {
            $query = "SELECT customer_id FROM customer WHERE customer_email = ?";
            $stmt = $this->executeQuery($query, [$email]);
            $result = $stmt->get_result();
            
            return $result->num_rows > 0;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Get customer by email for login
    public function getCustomerByEmail($email) {
        try {
            $query = "SELECT * FROM customer WHERE customer_email = ?";
            $stmt = $this->executeQuery($query, [$email]);
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    // Login customer method - validates email and password
    public function loginCustomer($email, $password) {
        try {
            // Get customer by email
            $customer = $this->getCustomerByEmail($email);
            
            if (!$customer) {
                return array("status" => false, "message" => "Invalid email or password!");
            }
            
            // Verify password
            if ($this->verifyPassword($password, $customer['customer_pass'])) {
                // Remove password from customer data before returning
                unset($customer['customer_pass']);
                return array(
                    "status" => true, 
                    "message" => "Login successful!", 
                    "customer" => $customer
                );
            } else {
                return array("status" => false, "message" => "Invalid email or password!");
            }
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Login failed: " . $e->getMessage());
        }
    }
    
    // Get customer by ID
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
            return null;
        }
    }
    
    // Edit customer
    public function editCustomer($customerId, $name, $email, $country, $city, $contact) {
        try {
            // Check if email exists for other customers
            $query = "SELECT customer_id FROM customer WHERE customer_email = ? AND customer_id != ?";
            $stmt = $this->executeQuery($query, [$email, $customerId]);
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return array("status" => false, "message" => "Email already exists!");
            }
            
            $updateQuery = "UPDATE customer SET customer_name = ?, customer_email = ?, customer_country = ?, customer_city = ?, customer_contact = ? WHERE customer_id = ?";
            
            $stmt = $this->executeQuery($updateQuery, [$name, $email, $country, $city, $contact, $customerId]);
            
            if ($stmt->affected_rows > 0) {
                return array("status" => true, "message" => "Customer updated successfully!");
            } else {
                return array("status" => false, "message" => "No changes made or customer not found!");
            }
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Error: " . $e->getMessage());
        }
    }
    
    // Delete customer
    public function deleteCustomer($customerId) {
        try {
            $query = "DELETE FROM customer WHERE customer_id = ?";
            $stmt = $this->executeQuery($query, [$customerId]);
            
            if ($stmt->affected_rows > 0) {
                return array("status" => true, "message" => "Customer deleted successfully!");
            } else {
                return array("status" => false, "message" => "Customer not found!");
            }
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Error: " . $e->getMessage());
        }
    }
    
    // Verify password
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    // Update customer image
    public function updateCustomerImage($customerId, $imagePath) {
        try {
            $query = "UPDATE customer SET customer_image = ? WHERE customer_id = ?";
            $stmt = $this->executeQuery($query, [$imagePath, $customerId]);
            
            if ($stmt->affected_rows > 0) {
                return array("status" => true, "message" => "Image updated successfully!");
            } else {
                return array("status" => false, "message" => "Failed to update image!");
            }
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Error: " . $e->getMessage());
        }
    }
    
    // Get all customers (for admin purposes)
    public function getAllCustomers() {
        try {
            $query = "SELECT customer_id, customer_name, customer_email, customer_country, customer_city, customer_contact, customer_image, user_role FROM customer";
            $stmt = $this->executeQuery($query);
            $result = $stmt->get_result();
            
            $customers = array();
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
            
            return array("status" => true, "customers" => $customers);
            
        } catch (Exception $e) {
            return array("status" => false, "message" => "Error: " . $e->getMessage());
        }
    }
}
?>