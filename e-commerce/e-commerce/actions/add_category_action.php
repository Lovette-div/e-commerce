<?php
require_once("/../controllers/category_controller.php");
session_start();

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);

if (add_category_ctr($user_id, $name)) {
    echo "Category added successfully";
} else {
    echo "Category already exists or failed to add";
}
