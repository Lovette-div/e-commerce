<?php
error_reporting(E_ALL); ini_set('display_errors',1);
require_once __DIR__.'/../settings/db_class.php';
require_once __DIR__.'/../settings/core.php';
require_once __DIR__.'/../controllers/product_controller.php';

// authorization (admin only)
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header('Location: ../view/all_product.php');
    exit;
}


$customer_name = $_SESSION['customer_name'] ?? 'Admin';

$db = new Database();
$conn = $db->getConnection();

// fetch categories and brands for selects
$cats = [];
$res = $conn->query("SELECT * FROM categories ORDER BY cat_name");
if ($res) while ($r = $res->fetch_assoc()) $cats[] = $r;

$brands = [];
$res2 = $conn->query("SELECT * FROM brands ORDER BY brand_name");
if ($res2) while ($r = $res2->fetch_assoc()) $brands[] = $r;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Launder - Admin - Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: url('/~lovette.philips/uploads/images/laundry1.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 200vh;
      display: flex;
      flex-direction: column;
  }

  footer {
      background: linear-gradient(135deg, #00bcd4 0%, #007bff 100%);
      color: white;
      text-align: center;
      padding: 15px 0;
      margin-top: auto;
      font-size: 14px;
  }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg shadow-sm"
     style="
       background: linear-gradient(135deg, #00bcd4 0%, #007bff 100%);
       color: white;
     ">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="#">Launder - Admin</a>
    <div class="ms-auto d-flex align-items-center">
      <span class="me-3 text-white-50">Hello, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Admin'); ?></span>
      <a href="../logout.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm me-2" style="color: #007bff;">Logout</a>
      <a href="../admin/category.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm me-2" style="color: #007bff;">Category</a>
      <a href="../admin/brand.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm" style="color: #007bff;">Brand</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="card p-4 mb-4">
    <h4>Add Product</h4>
    <form id="productForm" enctype="multipart/form-data">
      <input type="hidden" name="product_id" id="product_id" value="0">
      <div class="row g-3">
        <div class="col-md-6">
          <label>Title</label>
          <input name="title" id="title" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Price (GHS)</label>
          <input name="price" id="price" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label>Keyword</label>
          <input name="keyword" id="keyword" class="form-control">
        </div>
        <div class="col-md-6">
          <label>Category</label>
          <select name="cat_id" id="cat_id" class="form-select" required>
            <?php foreach($cats as $c) echo "<option value='{$c['cat_id']}'>".htmlspecialchars($c['cat_name'])."</option>"; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Brand</label>
          <select name="brand_id" id="brand_id" class="form-select" required>
            <?php foreach($brands as $b) echo "<option value='{$b['brand_id']}'>".htmlspecialchars($b['brand_name'])."</option>"; ?>
          </select>
        </div>
        <div class="col-md-12">
          <label>Description</label>
          <textarea name="description" id="description" rows="4" class="form-control"></textarea>
        </div>

        <div class="col-md-12">
          <label>Product Images (select multiple for bulk upload)</label>
          <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control">
          <div class="form-text">Images will be uploaded after product is created. You can upload in bulk.</div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary">Save Product</button>
          <button type="button" id="resetProductBtn" class="btn btn-outline-secondary">Reset</button>
        </div>
      </div>
    </form>
    <div id="productMsg" class="mt-2"></div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Title</th>
                <th>Price (GHS)</th>
                <th>Category</th>
                <th>Brand</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php 
            if (!empty($products)) {
                $count = 1;
                foreach ($products as $p) {

                    // Fetch product image
                    $img = "no_image.png"; // default
                    $images = $pc->get_product_images_ctr($p['product_id']);
                    
                    if (!empty($images)) {
                        $img = $images[0]['file_path']; // first image from product_images
                    }
            ?>

            <tr>
                <td><?php echo $count++; ?></td>

                <td>
                    <img src="../<?php echo $img; ?>" 
                         alt="Product Image" 
                         style="width:70px; height:70px; object-fit:cover; border-radius:5px;">
                </td>

                <td><?php echo $p['title']; ?></td>
                <td>GHS <?php echo number_format($p['price'], 2); ?></td>
                <td><?php echo $p['cat_name']; ?></td>
                <td><?php echo $p['brand_name']; ?></td>
                <td><?php echo date("d M Y", strtotime($p['created_at'])); ?></td>

                <td>
                    <a href="single_product.php?id=<?php echo $p['product_id']; ?>" 
                       class="btn btn-sm btn-info">View</a>
                </td>
            </tr>

            <?php 
                }
            } else { 
            ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">No products found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
  </div>
</div>

<!-- Edit modal (if you prefer modal editing) -->
<div class="modal fade" id="editProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <!-- can reuse form fields for edit -->
    </div>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <p class="mb-1">&copy; <?php echo date('Y'); ?> Launder E-Commerce Platform. All rights reserved.</p>
    <small>Developed by Team Launder | Contact: support@launderapp.com</small>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/product.js"></script>
</body>
</html>
