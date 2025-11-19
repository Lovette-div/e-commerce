<?php
require_once("../classes/category_class.php");

function add_category_ctr($user_id, $name) {
    $category = new Category();
    if ($category->category_exists($user_id, $name)) {
        return false; // category name must be unique
    }
    return $category->add_category($user_id, $name);
}

function get_categories_ctr($user_id) {
    $category = new Category();
    return $category->get_categories($user_id);
}

function update_category_ctr($id, $name) {
    $category = new Category();
    return $category->update_category($id, $name);
}

function delete_category_ctr($id) {
    $category = new Category();
    return $category->delete_category($id);
}
