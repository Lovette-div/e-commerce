<?php
require_once __DIR__ . '/../settings/db_class.php';

class Product extends Database {
    private $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    // Add a new product
    public function add($user_id, $title, $price, $description, $keyword, $cat_id, $brand_id) {
        $sql = "INSERT INTO products (title, price, description, keyword, cat_id, brand_id, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdssiii", $title, $price, $description, $keyword, $cat_id, $brand_id, $user_id);
        if ($stmt->execute()) {
            return ['success'=>true, 'msg'=>'Product added','product_id'=>$this->conn->insert_id];
        }
        return ['success'=>false, 'msg'=>'DB error: '.$this->conn->error];
    }

    // Update existing product
    public function update($product_id, $user_id, $title, $price, $description, $keyword, $cat_id, $brand_id) {
        $sql = "UPDATE products 
                SET title=?, price=?, description=?, keyword=?, cat_id=?, brand_id=?, updated_at=NOW() 
                WHERE product_id=? AND created_by=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdssiiii", $title, $price, $description, $keyword, $cat_id, $brand_id, $product_id, $user_id);
        if ($stmt->execute()) {
            return ['success'=>true,'msg'=>'Product updated'];
        }
        return ['success'=>false,'msg'=>'DB error: '.$this->conn->error];
    }

    // Get products created by a specific user
    public function getByUser($user_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.created_by = ?
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // View all products with optional pagination
    public function view_all_products($limit = 10, $offset = 0) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Count total products
    public function count_products() {
        $sql = "SELECT COUNT(*) AS total FROM products";
        $res = $this->conn->query($sql);
        $row = $res->fetch_assoc();
        return $row['total'];
    }

    // View single product details
    public function view_single_product($product_id) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    // Search products by title or description
    public function search_products($query, $limit = 10, $offset = 0) {
        $search = "%{$query}%";
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.title LIKE ? OR p.description LIKE ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", $search, $search, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Count search results
    public function count_search_results($query) {
        $search = "%{$query}%";
        $sql = "SELECT COUNT(*) AS total FROM products WHERE title LIKE ? OR description LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['total'];
    }

    // Filter products by category
    public function filter_products_by_category($cat_id, $limit = 10, $offset = 0) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.cat_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $cat_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Filter products by brand
    public function filter_products_by_brand($brand_id, $limit = 10, $offset = 0) {
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.brand_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $brand_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Extra Credit: search by keyword
    public function search_by_keyword($keyword, $limit = 10, $offset = 0) {
        $search = "%{$keyword}%";
        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.keyword LIKE ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $search, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Extra Credit: Advanced composite search
    public function advanced_search($filters, $limit = 10, $offset = 0) {
        $conditions = [];
        $params = [];
        $types = "";

        if (!empty($filters['query'])) {
            $conditions[] = "(p.title LIKE ? OR p.description LIKE ?)";
            $search = "%{$filters['query']}%";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (!empty($filters['category'])) {
            $conditions[] = "p.cat_id = ?";
            $params[] = $filters['category'];
            $types .= "i";
        }

        if (!empty($filters['brand'])) {
            $conditions[] = "p.brand_id = ?";
            $params[] = $filters['brand'];
            $types .= "i";
        }

        if (!empty($filters['min_price'])) {
            $conditions[] = "p.price >= ?";
            $params[] = $filters['min_price'];
            $types .= "d";
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "p.price <= ?";
            $params[] = $filters['max_price'];
            $types .= "d";
        }

        if (!empty($filters['keyword'])) {
            $conditions[] = "p.keyword LIKE ?";
            $search = "%{$filters['keyword']}%";
            $params[] = $search;
            $types .= "s";
        }

        $where = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT p.*, c.cat_name, b.brand_name,
                (SELECT file_path FROM product_images WHERE product_id = p.product_id LIMIT 1) AS image
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                {$where}
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Add product image
    public function addImage($product_id, $file_path) {
        $stmt = $this->conn->prepare("INSERT INTO product_images (product_id, file_path) VALUES (?, ?)");
        $stmt->bind_param("is", $product_id, $file_path);
        if ($stmt->execute()) {
            return ['success'=>true,'msg'=>'Image saved'];
        }
        return ['success'=>false,'msg'=>$this->conn->error];
    }

    // Get all images for a product
    public function getImages($product_id) {
        $stmt = $this->conn->prepare("SELECT file_path FROM product_images WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Get single product raw (no join)
    public function get($product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    // Get all products (no pagination)
    public function getAll() {
        $sql = "SELECT p.*, c.cat_name, b.brand_name
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                ORDER BY p.created_at DESC";
        $res = $this->conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function get_product_by_id($product_id) {
        $product_id = intval($product_id); // Sanitize input
        
        $sql = "SELECT p.*, c.cat_name, b.brand_name 
                FROM products p
                LEFT JOIN categories c ON p.cat_id = c.cat_id
                LEFT JOIN brands b ON p.brand_id = b.brand_id
                WHERE p.product_id = $product_id";
        
        return $this->fetchOne($sql);
    }
}
?>
