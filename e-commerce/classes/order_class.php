<?php
// Include the database connection file
require_once(__DIR__ . '/../settings/db_class.php');

/**
 * Order Class - handles all order-related database operations
 * This class extends the official database connection class
 */
class order_class extends Database {
    
    /**
     * Create a new order
     */
    public function create_order($customer_id, $invoice_no, $order_date, $order_status) {
        error_log("=== CREATE_ORDER METHOD CALLED ===");
        try {
            $conn = $this->getConnection();
            
            if (!$conn) {
                error_log("Failed to get database connection");
                return false;
            }
            
            $customer_id = (int)$customer_id;
            $invoice_no = $conn->real_escape_string($invoice_no);
            $order_date = $conn->real_escape_string($order_date);
            $order_status = $conn->real_escape_string($order_status);
            
            $sql = "INSERT INTO orders (customer_id, invoice_no, order_date, order_status) 
                    VALUES ($customer_id, '$invoice_no', '$order_date', '$order_status')";
            
            error_log("Executing SQL: $sql");
            
            $result = $this->query($sql);
            
            if ($result) {
                $order_id = $this->getInsertId();
                error_log("Order created successfully with ID: $order_id");
                
                if ($order_id > 0) {
                    return $order_id;
                } else {
                    error_log("Insert succeeded but ID is 0");
                    return false;
                }
            } else {
                error_log("Order creation failed");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Exception in create_order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add order details (products) to an order
     */
    public function add_order_details($order_id, $product_id, $qty) {
        try {
            $order_id = (int)$order_id;
            $product_id = (int)$product_id;
            $qty = (int)$qty;
            
            $sql = "INSERT INTO orderdetails (order_id, product_id, qty) 
                    VALUES ($order_id, $product_id, $qty)";
            
            error_log("Adding order detail - Order: $order_id, Product: $product_id, Qty: $qty");
            
            $this->query($sql);
            return true;
            
        } catch (Exception $e) {
            error_log("Error adding order details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record a payment for an order
     */
    public function record_payment($amount, $customer_id, $order_id, $currency, $payment_date, $payment_method = 'direct', $transaction_ref = null, $authorization_code = null, $payment_channel = null) {
        error_log("=== RECORD_PAYMENT METHOD CALLED ===");
        try {
            $conn = $this->getConnection();
            
            $amount = (float)$amount;
            $customer_id = (int)$customer_id;
            $order_id = (int)$order_id;
            $currency = $conn->real_escape_string($currency);
            $payment_date = $conn->real_escape_string($payment_date);
            $payment_method = $conn->real_escape_string($payment_method);
            $transaction_ref = $transaction_ref ? $conn->real_escape_string($transaction_ref) : null;
            $authorization_code = $authorization_code ? $conn->real_escape_string($authorization_code) : null;
            $payment_channel = $payment_channel ? $conn->real_escape_string($payment_channel) : null;
            
            // Build SQL with optional fields
            $columns = "(amt, customer_id, order_id, currency, payment_date, payment_method";
            $values = "($amount, $customer_id, $order_id, '$currency', '$payment_date', '$payment_method'";
            
            if ($transaction_ref) {
                $columns .= ", transaction_ref";
                $values .= ", '$transaction_ref'";
            }
            if ($authorization_code) {
                $columns .= ", authorization_code";
                $values .= ", '$authorization_code'";
            }
            if ($payment_channel) {
                $columns .= ", payment_channel";
                $values .= ", '$payment_channel'";
            }
            
            $columns .= ")";
            $values .= ")";
            
            $sql = "INSERT INTO payment $columns VALUES $values";
            
            error_log("Executing SQL: $sql");
            
            if ($this->query($sql)) {
                $payment_id = $this->getInsertId();
                error_log("Payment recorded successfully with ID: $payment_id");
                return $payment_id;
            } else {
                error_log("Payment recording failed");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error recording payment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all orders for a user
     */
    public function get_user_orders($customer_id) {
        try {
            $customer_id = (int)$customer_id;
            
            $sql = "SELECT 
                        o.order_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        p.amt as total_amount,
                        p.currency,
                        COUNT(od.product_id) as item_count
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    LEFT JOIN orderdetails od ON o.order_id = od.order_id
                    WHERE o.customer_id = $customer_id
                    GROUP BY o.order_id
                    ORDER BY o.order_date DESC, o.order_id DESC";
            
            $orders = $this->fetchAll($sql);
            return $orders ? $orders : false;
            
        } catch (Exception $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get details of a specific order
     */
    public function get_order_details($order_id, $customer_id) {
        try {
            $order_id = (int)$order_id;
            $customer_id = (int)$customer_id;
            
            $sql = "SELECT 
                        o.order_id,
                        o.invoice_no,
                        o.order_date,
                        o.order_status,
                        o.customer_id,
                        p.amt as total_amount,
                        p.currency,
                        p.payment_date
                    FROM orders o
                    LEFT JOIN payment p ON o.order_id = p.order_id
                    WHERE o.order_id = $order_id AND o.customer_id = $customer_id";
            
            $order = $this->fetchOne($sql);
            return $order ? $order : false;
            
        } catch (Exception $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all products in a specific order
     */
    public function get_order_products($order_id) {
        try {
            $order_id = (int)$order_id;
            
            $sql = "SELECT 
                        od.product_id,
                        od.qty,
                        p.product_title,
                        p.product_price,
                        p.product_image,
                        (od.qty * p.product_price) as subtotal
                    FROM orderdetails od
                    INNER JOIN products p ON od.product_id = p.product_id
                    WHERE od.order_id = $order_id";
            
            $products = $this->fetchAll($sql);
            return $products ? $products : false;
            
        } catch (Exception $e) {
            error_log("Error getting order products: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update order status
     */
    public function update_order_status($order_id, $order_status) {
        try {
            $conn = $this->getConnection();
            $order_id = (int)$order_id;
            $order_status = $conn->real_escape_string($order_status);
            
            $sql = "UPDATE orders SET order_status = '$order_status' WHERE order_id = $order_id";
            
            error_log("Updating order status: $order_id to $order_status");
            
            $this->query($sql);
            return true;
            
        } catch (Exception $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
}
?>