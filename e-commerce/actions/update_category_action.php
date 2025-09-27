<?php
require_once("/../controllers/category_controller.php");

$id = $_POST['id'];
$name = trim($_POST['name']);

if (update_category_ctr($id, $name)) {
    echo "Category updated successfully";
} else {
    echo "Failed to update category";
}
