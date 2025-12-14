<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 2).'/settings/core.php';
require_once dirname(__DIR__, 2).'/settings/db_class.php';

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['status'=>false, 'message'=>'Unauthorized']);
    exit;
}

// Use $_POST instead of JSON decode
$brand_id = intval($_POST['brand_id'] ?? 0);

if ($brand_id == 0) {
    echo json_encode(['status'=>false, 'message'=>'Brand ID is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("DELETE FROM brands WHERE brand_id=?");
$stmt->bind_param("i", $brand_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['status'=>true, 'message'=>'Brand deleted successfully']);
} else {
    echo json_encode(['status'=>false, 'message'=>'Database error: '.$conn->error]);
}