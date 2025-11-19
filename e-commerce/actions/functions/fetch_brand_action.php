<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__, 2).'/settings/core.php';
require_once dirname(__DIR__, 2).'/settings/db_class.php';

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['customer_id'];
$sql = "SELECT b.brand_id, b.brand_name, b.cat_id, c.cat_name
        FROM brands b
        JOIN categories c ON b.cat_id = c.cat_id
        WHERE b.created_by = ?
        ORDER BY c.cat_name, b.brand_name";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rows);
