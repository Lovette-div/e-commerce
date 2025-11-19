<?php
require_once("/../controllers/category_controller.php");

$id = $_POST['id'];

if (delete_category_ctr($id)) {
    echo "Category deleted successfully";
} else {
    echo "Failed to delete category";
}
