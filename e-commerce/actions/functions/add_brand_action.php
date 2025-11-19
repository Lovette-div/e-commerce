<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 2).'/settings/core.php';
require_once dirname(__DIR__, 2).'/settings/db_class.php';

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success'=>false, 'msg'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$brand_name = trim($data['brand_name'] ?? '');
$cat_id = intval($data['cat_id'] ?? 0);

if ($brand_name=='' || $cat_id==0) {
    echo json_encode(['success'=>false,'msg'=>'Missing fields']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['customer_id'];

// check duplicates
$check = $conn->prepare("SELECT COUNT(*) FROM brands WHERE brand_name=? AND cat_id=? AND created_by=?");
$check->bind_param("sii", $brand_name, $cat_id, $user_id);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

if ($count > 0) {
    echo json_encode(['success'=>false, 'msg'=>'Brand already exists for this category']);
    exit;
}

// insert new brand
$stmt = $conn->prepare("INSERT INTO brands (brand_name, cat_id, created_by, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sii", $brand_name, $cat_id, $user_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success'=>true,'msg'=>'Brand added successfully']);
} else {
    echo json_encode(['success'=>false,'msg'=>'Database error: '.$conn->error]);
}
