<?php
require_once(dirname(__FILE__).'/../classes/cart_class.php');

/**
 * Cart Controller - Wrapper for cart operations
 * Works with product_controller to ensure data consistency
 */
class CartController {
    
    private $cart;
    
    public function __construct() {
        $this->cart = new Cart();
    }
    
    /**
     * Add product to cart
     * @param array $params ['product_id', 'customer_id', 'qty']
     * @return bool
     */
    public function add_to_cart_ctr($params) {
        // Validate product exists and get current price
        require_once(dirname(__FILE__).'/product_controller.php');
        $productController = new ProductController();
        $product = $productController->get_product_by_id_ctr($params['product_id']);
        
        if (!$product) {
            return false; // Product doesn't exist
        }
        
        // Add to cart with validated data
        return $this->cart->add_to_cart(
            $params['product_id'], 
            $params['customer_id'], 
            $params['qty'] ?? 1
        );
    }
    
    /**
     * Update cart item quantity
     * @param int $cart_id
     * @param int $qty
     * @return bool
     */
    public function update_cart_item_ctr($cart_id, $qty) {
        return $this->cart->update_cart_quantity($cart_id, $qty);
    }
    
    /**
     * Remove item from cart
     * @param int $cart_id
     * @return bool
     */
    public function remove_from_cart_ctr($cart_id) {
        return $this->cart->remove_from_cart($cart_id);
    }
    
    /**
     * Get user's cart items with current product details and pricing
     * Ensures pricing consistency with product_controller
     * @param int $customer_id
     * @return array
     */
    public function get_user_cart_ctr($customer_id) {
        require_once(dirname(__FILE__).'/product_controller.php');
        $productController = new ProductController();
        
        // Get cart items
        $cart_items = $this->cart->get_user_cart($customer_id);
        
        // Verify each item still exists and has correct pricing
        $validated_items = [];
        foreach ($cart_items as $item) {
            $product = $productController->get_product_by_id_ctr($item['p_id']);
            
            if ($product) {
                // Use current product price from database
                $item['price'] = $product['price'];
                $item['title'] = $product['title'];
                $item['subtotal'] = $item['qty'] * $product['price'];
                $validated_items[] = $item;
            }
        }
        
        return $validated_items;
    }
    
    /**
     * Empty user's cart
     * @param int $customer_id
     * @return bool
     */
    public function empty_cart_ctr($customer_id) {
        return $this->cart->empty_cart($customer_id);
    }
    
    /**
     * Get cart count
     * @param int $customer_id
     * @return int
     */
    public function get_cart_count_ctr($customer_id) {
        return $this->cart->get_cart_count($customer_id);
    }
    
    /**
     * Get cart total with current pricing
     * Ensures total is calculated with latest product prices
     * @param int $customer_id
     * @return float
     */
    public function get_cart_total_ctr($customer_id) {
        // Get validated cart items with current pricing
        $cart_items = $this->get_user_cart_ctr($customer_id);
        
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
    
    /**
     * Check if product exists in cart
     * @param int $product_id
     * @param int $customer_id
     * @return bool
     */
    public function product_exists_in_cart_ctr($product_id, $customer_id) {
        return $this->cart->product_exists_in_cart($product_id, $customer_id);
    }
    
    /**
     * Validate cart items before checkout
     * Ensures all products exist and have valid pricing
     * @param int $customer_id
     * @return array ['valid' => bool, 'message' => string, 'items' => array]
     */
    public function validate_cart_for_checkout_ctr($customer_id) {
        require_once(dirname(__FILE__).'/product_controller.php');
        $productController = new ProductController();
        
        $cart_items = $this->cart->get_user_cart($customer_id);
        
        if (empty($cart_items)) {
            return [
                'valid' => false,
                'message' => 'Your cart is empty',
                'items' => []
            ];
        }
        
        $validated_items = [];
        foreach ($cart_items as $item) {
            $product = $productController->get_product_by_id_ctr($item['p_id']);
            
            if (!$product) {
                return [
                    'valid' => false,
                    'message' => 'Some products in your cart are no longer available',
                    'items' => []
                ];
            }
            
            // Use current product price
            $item['price'] = $product['price'];
            $item['title'] = $product['title'];
            $item['subtotal'] = $item['qty'] * $product['price'];
            $validated_items[] = $item;
        }
        
        return [
            'valid' => true,
            'message' => 'Cart is valid',
            'items' => $validated_items
        ];
    }
}
?>