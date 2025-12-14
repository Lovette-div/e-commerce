<?php
// require_once dirname(__DIR__) . '/settings/db_class.php';

// class Category extends Database {

//     // Fetch categories created by a specific user
//     public function getCategories($customer_id) {
//         $sql = "SELECT cat_id, cat_name, created_at FROM categories WHERE created_by = ?";
//         return $this->db_fetch_all($sql, [$customer_id]);
//     }

//     // Add a category
//     public function addCategory($cat_name, $customer_id) {
//         $sql = "INSERT INTO categories (cat_name, created_by) VALUES (?, ?)";
//         return $this->db_query($sql, [$cat_name, $customer_id]);
//     }

//     // Update category
//     public function updateCategory($cat_id, $cat_name) {
//         $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
//         return $this->db_query($sql, [$cat_name, $cat_id]);
//     }

//     // Delete category
//     public function deleteCategory($cat_id) {
//         $sql = "DELETE FROM categories WHERE cat_id = ?";
//         return $this->db_query($sql, [$cat_id]);
//     }
// }


require_once('../settings/db_class.php');

class Category extends Database {

    // Fetch categories created by a specific user
    public function getCategories($customer_id) {
        $sql = "SELECT cat_id, cat_name, created_at FROM categories WHERE created_by = ?";
        $stmt = $this->executeQuery($sql, [$customer_id]);
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Add a category
    public function addCategory($cat_name, $customer_id) {
        $sql = "INSERT INTO categories (cat_name, created_by) VALUES (?, ?)";
        $this->executeQuery($sql, [$cat_name, $customer_id]);
        return $this->getInsertId();
    }

    // Update category
    public function updateCategory($cat_id, $cat_name) {
        $sql = "UPDATE categories SET cat_name = ? WHERE cat_id = ?";
        $this->executeQuery($sql, [$cat_name, $cat_id]);
        return true;
    }

    // Delete category
    public function deleteCategory($cat_id) {
        $sql = "DELETE FROM categories WHERE cat_id = ?";
        $this->executeQuery($sql, [$cat_id]);
        return true;
    }

    // Fetch all categories
    public function getAll() {
        $sql = "SELECT cat_id, cat_name FROM categories ORDER BY cat_name ASC";
        $stmt = $this->executeQuery($sql, []); // no parameters needed
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

}
?>
