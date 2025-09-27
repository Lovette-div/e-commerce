<?php
require_once("/../db/core.php"); 

class Category extends db_connection {

    // Add category
    public function add_category($user_id, $name) {
        $sql = "INSERT INTO categories (user_id, name) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$user_id, $name]);
    }

    // Get all categories created by user
    public function get_categories($user_id) {
        $sql = "SELECT * FROM categories WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    // Update category name
    public function update_category($id, $name) {
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $id]);
    }

    // Delete category
    public function delete_category($id) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    //checking if name already exists
    public function category_exists($user_id, $name) {
        $sql = "SELECT * FROM categories WHERE user_id = ? AND name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_id, $name]);
        return $stmt->fetch();
    }
}
