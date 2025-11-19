<?php
require_once __DIR__.'/../classes/brand_class.php';
class BrandController {
    private $m;
    public function __construct() {
        $this->m = new Brand();
    }
    public function add_brand_ctr($user_id, $brand_name, $cat_id) {
        return $this->m->add($user_id, $brand_name, $cat_id);
    }
    public function update_brand_ctr($brand_id, $user_id, $brand_name, $cat_id) {
        return $this->m->update($brand_id, $user_id, $brand_name, $cat_id);
    }
    public function delete_brand_ctr($brand_id, $user_id) {
        return $this->m->delete($brand_id, $user_id);
    }
    public function fetch_by_user_ctr($user_id) {
        return $this->m->getByUser($user_id);
    }
    public function fetch_all_ctr() {
        return $this->m->getAll();
    }
}
?>