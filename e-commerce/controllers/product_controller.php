<?php
require_once __DIR__ . '/../classes/product_class.php';

class ProductController {
    private $m;
    public function __construct() {
        $this->m = new Product();
    }

    public function add_product_ctr($user_id, $title, $price, $description, $keyword, $cat_id, $brand_id) {
        return $this->m->add($user_id, $title, $price, $description, $keyword, $cat_id, $brand_id);
    }

    public function update_product_ctr($product_id, $user_id, $title, $price, $description, $keyword, $cat_id, $brand_id) {
        return $this->m->update($product_id, $user_id, $title, $price, $description, $keyword, $cat_id, $brand_id);
    }

    public function fetch_by_user_ctr($user_id) {
        return $this->m->getByUser($user_id);
    }

    public function fetch_all_ctr() {
        return $this->m->getAll();
    }

    public function add_product_image_ctr($product_id, $path) {
        return $this->m->addImage($product_id, $path);
    }

    public function get_product_images_ctr($product_id) {
        return $this->m->getImages($product_id);
    }

    public function get_product_ctr($product_id) {
        return $this->m->get($product_id);
    }

    function view_all_products_ctr() {
    $pc = new ProductController();
    return $pc->fetch_all_ctr();  // uses getAll() from Product class
    }

    function view_single_product_ctr($id) {
        $pc = new ProductController();
        return $pc->get_product_ctr($id);
    }


    function search_products_ctr($query) {
        return $this->m->search_products($query);  
    }

    function filter_products_by_category_ctr($cat_id) {
        return $this->m->filter_products_by_category($cat_id);  
    }

    
    function filter_products_by_brand_ctr($brand_id) {
        return $this->m->filter_products_by_brand($brand_id);  
    }

    function advanced_search_ctr($filters) {
       return $this->m->advanced_search($filters); //credit
    }

    function search_by_keyword_ctr($keyword) {
        return $this->m->search_by_keyword($keyword); // extra credit
    }

    public function get_product_by_id_ctr($product_id) {
    $product = new Product();
    return $product->get_product_by_id($product_id);
    }
}
?>
