<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/settings/core.php';
require_once dirname(__DIR__, 2) . '/settings/db_class.php';
require_once dirname(__DIR__, 2) . '/controllers/product_controller.php';

try {
    if (!isset($_SESSION['customer_id'])) throw new Exception('Not logged in');
    $user_id = intval($_SESSION['customer_id']);
    $product_id = intval($_POST['product_id'] ?? 0);
    if ($product_id <= 0) throw new Exception('Missing product_id');

    if (empty($_FILES['images'])) throw new Exception('No files uploaded');

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $uploaded = [];

    // Point directly to the real uploads folder (outside e-commerce)
    $uploadsBase = '/home/lovette.philips/public_html/uploads';

    // Check that uploads/ exists and is writable
    if (!is_dir($uploadsBase) || !is_writable($uploadsBase)) {
        throw new Exception('Uploads directory not found or not writable: ' . $uploadsBase);
    }

    // Create subdirectories inside uploads/ (user and product)
    $userDir = $uploadsBase . "/u{$user_id}";
    $prodDir = $userDir . "/p{$product_id}";

    // Only create subdirectories if allowed
    if (!is_dir($userDir) && !mkdir($userDir, 0755, true)) {
        throw new Exception("Cannot create user folder inside uploads/");
    }
    if (!is_dir($prodDir) && !mkdir($prodDir, 0755, true)) {
        throw new Exception("Cannot create product folder inside uploads/");
    }

    $files = $_FILES['images'];

    for ($i = 0; $i < count($files['name']); $i++) {
        $tmp = $files['tmp_name'][$i];
        $name = basename($files['name'][$i]);
        $type = $files['type'][$i];
        $error = $files['error'][$i];

        if ($error !== UPLOAD_ERR_OK) continue;
        if (!in_array($type, $allowed)) continue;

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = uniqid('img_', true) . "." . $ext;
        $target = $prodDir . "/" . $newName;

        // Verify we’re still inside uploads folder (safety)
        $realTargetDir = realpath(dirname($target));
        if ($realTargetDir === false || strpos($realTargetDir, realpath($uploadsBase)) !== 0) continue;

        if (!move_uploaded_file($tmp, $target)) continue;

        // Path relative to web root (for storage in DB)
        $relPath = "uploads/u{$user_id}/p{$product_id}/" . $newName;

        $ctrl = new ProductController();
        $imgRes = $ctrl->add_product_image_ctr($product_id, $relPath);
        if ($imgRes['success']) {
            $uploaded[] = $relPath;
        }
    }

    if (empty($uploaded)) {
        echo json_encode(['success' => false, 'msg' => 'No images saved']);
    } else {
        echo json_encode(['success' => true, 'msg' => 'Images uploaded', 'files' => $uploaded]);
    }

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'msg' => 'Server error: ' . $e->getMessage()]);
}
