<?php
// require_once("../classes/category_class.php");

// class CategoryController {
//     private $category;

//     public function __construct() {
//         $this->category = new Category();
//     }

//     public function addCategoryCtr($name, $user_id) {
//         $name = trim($name);
//         if (empty($name)) return ['status' => false, 'message' => 'Category name required'];
//         // Optionally check uniqueness for this user
//         $all = $this->category->getByUser($user_id);
//         if ($all['status']) {
//             foreach ($all['categories'] as $c) {
//                 if (strcasecmp($c['cat_name'], $name) === 0) {
//                     return ['status' => false, 'message' => 'Category name already exists'];
//                 }
//             }
//         }
//         $id = $this->category->add($name, $user_id);
//         if ($id) return ['status' => true, 'cat_id' => $id, 'message' => 'Category added'];
//         return ['status' => false, 'message' => 'Failed to add category'];
//     }

//     function fetch_categories_ctr($customer_id) {
//     $category = new Category();
//     return $category->getCategories($customer_id);
//     }

//     public function updateCategoryCtr($cat_id, $name, $customer_id) {
//         $name = trim($name);
//         if (empty($name)) return ['status' => false, 'message' => 'Category name required'];
//         return $this->category->update($cat_id, $name, $customer_id);
//     }

//     public function deleteCategoryCtr($cat_id, $customer_id) {
//         if (empty($cat_id)) return ['status' => false, 'message' => 'Invalid category id'];
//         return $this->category->delete($cat_id, $customer_id);
//     }
// }


require_once('../classes/category_class.php');

// Controller function to fetch all categories for a customer
function fetch_categories_ctr($customer_id) {
    $category = new Category();
    return $category->getCategories($customer_id);
}

// (Optional) other controller functions if needed:
function add_category_ctr($cat_name, $customer_id) {
    $category = new Category();
    return $category->addCategory($cat_name, $customer_id);
}

function update_category_ctr($cat_id, $cat_name) {
    $category = new Category();
    return $category->updateCategory($cat_id, $cat_name);
}

function delete_category_ctr($cat_id) {
    $category = new Category();
    return $category->deleteCategory($cat_id);
}

function get_all_categories_ctr() {
    $category = new Category();
    return $category->getAll();
}


?>
