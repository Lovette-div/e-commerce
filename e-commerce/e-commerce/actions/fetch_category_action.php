<?php
require_once("/../controllers/category_controller.php");
session_start();

$user_id = $_SESSION['user_id'];
$categories = get_categories_ctr($user_id);
echo json_encode($categories);
