<?php
require_once __DIR__ . '/../settings/db_class.php';

class Brand extends Database {
    private $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function add($customer_id, $brand_name, $cat_id) {
        // Check duplicate
        $sql = "SELECT COUNT(*) as cnt FROM brands WHERE brand_name = ? AND cat_id = ? AND created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $brand_name, $cat_id, $customer_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();

        if ($r['cnt'] > 0) {
            return ['success'=>false, 'msg'=>'Brand with that category already exists.'];
        }

        // Insert new brand
        $sql = "INSERT INTO brands (brand_name, cat_id, created_by, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $brand_name, $cat_id, $customer_id);
        $ok = $stmt->execute();

        if ($ok) {
            $id = $this->conn->insert_id;
            return ['success'=>true, 'msg'=>'Brand added', 'id'=>$id];
        }

        return ['success'=>false, 'msg'=>'DB error: '.$this->conn->error];
    }

    public function update($brand_id, $customer_id, $brand_name, $cat_id) {
        // Check for duplicate
        $sql = "SELECT COUNT(*) as cnt FROM brands WHERE brand_name = ? AND cat_id = ? AND created_by = ? AND brand_id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siii", $brand_name, $cat_id, $customer_id, $brand_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();

        if ($r['cnt'] > 0) {
            return ['success'=>false, 'msg'=>'Another brand with same name and category exists.'];
        }

        // Update brand
        $sql = "UPDATE brands SET brand_name = ?, cat_id = ? WHERE brand_id = ? AND created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siii", $brand_name, $cat_id, $brand_id, $customer_id);
        $ok = $stmt->execute();

        return $ok
            ? ['success'=>true, 'msg'=>'Brand updated']
            : ['success'=>false, 'msg'=>'DB error: '.$this->conn->error];
    }

    public function delete($brand_id, $customer_id) {
        $sql = "DELETE FROM brands WHERE brand_id = ? AND created_by = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $brand_id, $customer_id);
        $ok = $stmt->execute();
        return $ok
            ? ['success'=>true, 'msg'=>'Brand deleted']
            : ['success'=>false, 'msg'=>'DB error: '.$this->conn->error];
    }

    public function getByUser($customer_id) {
        $sql = "SELECT b.*, c.cat_name
                FROM brands b
                JOIN categories c ON b.cat_id = c.cat_id
                WHERE b.created_by = ?
                ORDER BY c.cat_name, b.brand_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll() {
        $sql = "SELECT b.*, c.cat_name
                FROM brands b
                JOIN categories c ON b.cat_id = c.cat_id
                ORDER BY c.cat_name, b.brand_name";
        $res = $this->conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>
