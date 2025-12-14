<?php
require_once(dirname(__FILE__).'/../settings/db_class.php');

/**
 * Cart class for managing shopping cart operations
 */
class Cart extends Database {
    
    /**
     * Add a product to the cart
     * If product already exists, increment quantity
     * @param int $product_id
     * @param int $customer_id
     * @param int $qty
     * @return bool
     */
    public function add_to_cart($product_id, $customer_id, $qty = 1) {
        $product_id = intval($product_id);
        $customer_id = intval($customer_id);
        $qty = intval($qty);
        
        // Check if product already exists in cart
        $check_sql = "SELECT cart_id, qty FROM cart 
                      WHERE p_id = $product_id AND c_id = $customer_id";
        $result = $this->query($check_sql);
        
        if ($result && $result->num_rows > 0) {
            // Product exists, update quantity
            $row = $result->fetch_assoc();
            $new_qty = $row['qty'] + $qty;
            $cart_id = $row['cart_id'];
            
            $update_sql = "UPDATE cart SET qty = $new_qty WHERE cart_id = $cart_id";
            return $this->query($update_sql);
        } else {
            // Product doesn't exist, insert new
            $insert_sql = "INSERT INTO cart (p_id, c_id, qty) 
                          VALUES ($product_id, $customer_id, $qty)";
            return $this->query($insert_sql);
        }
    }
    
    /**
     * Update the quantity of a product in the cart
     * @param int $cart_id
     * @param int $qty
     * @return bool
     */
    public function update_cart_quantity($cart_id, $qty) {
        $cart_id = intval($cart_id);
        $qty = intval($qty);
        
        if ($qty <= 0) {
            // If quantity is 0 or negative, remove the item
            return $this->remove_from_cart($cart_id);
        }
        
        $sql = "UPDATE cart SET qty = $qty WHERE cart_id = $cart_id";
        return $this->query($sql);
    }
    
    /**
     * Remove a product from the cart
     * @param int $cart_id
     * @return bool
     */
    public function remove_from_cart($cart_id) {
        $cart_id = intval($cart_id);
        $sql = "DELETE FROM cart WHERE cart_id = $cart_id";
        return $this->query($sql);
    }
    
    /**
     * Get all cart items for a user with product details
     * @param int $customer_id
     * @return array
     */
    public function get_user_cart($customer_id) {
        $customer_id = intval($customer_id);
        
        $sql = "SELECT c.cart_id, c.p_id, c.qty, 
                       p.title, p.price,
                       (SELECT pi.file_path FROM product_images pi 
                        WHERE pi.product_id = c.p_id 
                        LIMIT 1) as product_image,
                       (c.qty * p.price) as subtotal
                FROM cart c
                INNER JOIN products p ON c.p_id = p.product_id
                WHERE c.c_id = $customer_id
                ORDER BY c.cart_id DESC";
        
        $result = $this->query($sql);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    /**
     * Empty the entire cart for a user
     * @param int $customer_id
     * @return bool
     */
    public function empty_cart($customer_id) {
        $customer_id = intval($customer_id);
        $sql = "DELETE FROM cart WHERE c_id = $customer_id";
        return $this->query($sql);
    }
    
    /**
     * Check if a product exists in user's cart
     * @param int $product_id
     * @param int $customer_id
     * @return bool
     */
    public function product_exists_in_cart($product_id, $customer_id) {
        $product_id = intval($product_id);
        $customer_id = intval($customer_id);
        
        $sql = "SELECT cart_id FROM cart 
                WHERE p_id = $product_id AND c_id = $customer_id";
        $result = $this->query($sql);
        
        return ($result && $result->num_rows > 0);
    }
    
    /**
     * Get cart count for a user
     * @param int $customer_id
     * @return int
     */
    public function get_cart_count($customer_id) {
        $customer_id = intval($customer_id);
        
        $sql = "SELECT SUM(qty) as total FROM cart WHERE c_id = $customer_id";
        $result = $this->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ? intval($row['total']) : 0;
        }
        return 0;
    }
    
    /**
     * Get cart total for a user
     * @param int $customer_id
     * @return float
     */
    public function get_cart_total($customer_id) {
        $customer_id = intval($customer_id);
        
        $sql = "SELECT SUM(c.qty * p.price) as total
                FROM cart c
                INNER JOIN products p ON c.p_id = p.product_id
                WHERE c.c_id = $customer_id";
        
        $result = $this->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ? floatval($row['total']) : 0.00;
        }
        return 0.00;
    }
}
?>