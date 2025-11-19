<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../settings/db_class.php';
require_once __DIR__.'/../settings/core.php';
require_once __DIR__.'/../controllers/brand_controller.php';

// Only admins can access
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header('Location: ../view/view_all_products.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$result = $conn->query("SELECT * FROM categories ORDER BY cat_name");

$cats = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cats[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Launder - Admin - Brands</title>
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

  .container-main {
    flex: 1;
    padding: 40px 20px;
  }

  .card-custom p-4 mb-4{
    max-width: 600px;
  }
  .card-custom {
    border-radius: 1rem;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.09);
    backdrop-filter: blur(7px);
    margin-bottom: 30px;
  }
  /* .form-wrapper {
    max-width: 600px; /* Half-width look */
    /* margin: 0 auto 40px auto; */
   */
  .table thead {
    background: #f8f9fa;
  }

  table td, table th {
    vertical-align: middle !important;
  }

  .btn-primary { background-color: #00bcd4; border: none; }
  .btn-primary:hover { background-color: #0097a7; }
  .btn-danger { background-color: #dc3545; border: none; }
  .btn-danger:hover { background-color: #b52b37; }

  #brandTable .btn-edit, #brandTable .btn-delete {
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 0 4px;
    background-color: transparent;
  }
  #brandTable .btn-edit { border: 2px solid #3b82f6; color: #3b82f6; }
  #brandTable .btn-edit:hover { background-color: #3b82f6; color: white; transform: translateY(-1px); }
  #brandTable .btn-delete { border: 2px solid #ef4444; color: #ef4444; }
  #brandTable .btn-delete:hover { background-color: #ef4444; color: white; transform: translateY(-1px); }

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
<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm"
     style="background: linear-gradient(135deg, #00bcd4 0%, #007bff 100%); color: white;">
  <div class="container">
    <a class="navbar-brand fw-bold text-white" href="#">Launder Admin</a>
    <div class="ms-auto d-flex align-items-center">
      <span class="me-3 text-white-50">Hello, <?php echo htmlspecialchars($_SESSION['customer_name'] ?? 'Admin'); ?></span>
      <a href="../logout.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm me-2" style="color: #007bff;">Logout</a>
      <a href="../admin/category.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm me-2" style="color: #007bff;">Category</a>
      <a href="../admin/product.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm" style="color: #007bff;">Add Product</a>
    </div>
  </div>
</nav>

<div class="container-main">
  <div class="card-custom p-4 mb-4">
    <h4 class="mb-3">Add Brand</h4>
    <form id="brandForm">
      <input type="hidden" id="brand_id" name="brand_id" value="0">
      <div class="mb-3">
        <label class="form-label">Brand Name</label>
        <input id="brand_name" name="brand_name" class="form-control" placeholder="Enter brand name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Category</label>
        <select id="cat_id" name="cat_id" class="form-select" required>
          <?php foreach($cats as $c) echo "<option value='{$c['cat_id']}'>".htmlspecialchars($c['cat_name'])."</option>"; ?>
        </select>
      </div>
      <div class="d-flex">
        <button type="submit" class="btn btn-info me-2 text-white">Add Brand</button>
        <button type="button" id="resetBtn" class="btn btn-outline-secondary">Reset</button>
      </div>
    </form>
  </div>

  <!-- Table below the form -->
  <div class="card-custom p-4">
    <h4 class="mb-3">Existing Brands</h4>
    <div class="table-responsive" style="max-height:450px; overflow:auto;">
      <table class="table table-hover align-middle" id="brandTable">
        <thead><tr><th>ID</th><th>Brand</th><th>Category</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Edit Brand Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editBrandForm" class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #00bcd4 0%, #007bff 100%); color:white;">
        <h5 class="modal-title">Edit Brand</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_brand_id" name="brand_id">
        <div class="mb-3">
          <label class="form-label">Brand Name</label>
          <input type="text" class="form-control" id="edit_brand_name" name="brand_name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Category</label>
          <select class="form-select" id="edit_cat_id" name="cat_id" required></select>
        </div>
        <div id="editMsg"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
    </form>
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
<script src="../js/brand.js"></script>
</body>
</html>
