<?php
// Product Actions - Handles all product-related AJAX requests
session_start();
header('Content-Type: application/json');

require_once('../controllers/product_controller.php');

// Check if action is specified
if (!isset($_GET['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

$action = $_GET['action'];

switch ($action) {
    case 'get_all':
        // Get all products
        $products = view_all_products_ctr();
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'get_single':
        // Get single product
        if (!isset($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit();
        }
        
        $product_id = intval($_GET['id']);
        $product = view_single_product_ctr($product_id);
        
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        break;
        
    case 'search':
        // Search products
        if (!isset($_GET['query'])) {
            echo json_encode(['success' => false, 'message' => 'Search query required']);
            exit();
        }
        
        $query = trim($_GET['query']);
        $products = search_products_ctr($query);
        
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'filter_by_category':
        // Filter by category
        if (!isset($_GET['cat_id'])) {
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit();
        }
        
        $cat_id = intval($_GET['cat_id']);
        $products = filter_products_by_category_ctr($cat_id);
        
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'filter_by_brand':
        // Filter by brand
        if (!isset($_GET['brand_id'])) {
            echo json_encode(['success' => false, 'message' => 'Brand ID required']);
            exit();
        }
        
        $brand_id = intval($_GET['brand_id']);
        $products = filter_products_by_brand_ctr($brand_id);
        
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'advanced_search':
        // Advanced search with multiple filters (Extra Credit)
        $filters = [
            'query' => isset($_GET['query']) ? trim($_GET['query']) : '',
            'category' => isset($_GET['category']) ? intval($_GET['category']) : 0,
            'brand' => isset($_GET['brand']) ? intval($_GET['brand']) : 0,
            'min_price' => isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0,
            'max_price' => isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0
        ];
        
        $products = advanced_search_ctr($filters);
        
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'search_by_keyword':
        // Keyword search (Extra Credit)
        if (!isset($_GET['keyword'])) {
            echo json_encode(['success' => false, 'message' => 'Keyword required']);
            exit();
        }
        
        $keyword = trim($_GET['keyword']);
        $products = search_by_keyword_ctr($keyword);
        
        if ($products) {
            echo json_encode(['success' => true, 'data' => $products]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No products found']);
        }
        break;
        
    case 'get_images':
        // Get product images
        if (!isset($_GET['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit();
        }
        
        $product_id = intval($_GET['product_id']);
        $images = get_product_images_ctr($product_id);
        
        if ($images) {
            echo json_encode(['success' => true, 'data' => $images]);
        } else {
            echo json_encode(['success' => true, 'data' => [], 'message' => 'No images found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>