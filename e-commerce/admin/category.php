<?php
require_once('../settings/core.php');
session_start();

//only admins can access
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
    header('Location: ../login/login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Category Management — The Launder</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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

  .overlay {
    background-color: rgba(255, 255, 255, 0.85);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
  }

  .card {
    border-radius: 1rem;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    backdrop-filter: blur(6px);
  }

  table td,
  table th {
    vertical-align: middle !important;
  }

  .btn-primary {
    background-color: #00bcd4;
    border: none;
  }

  .btn-primary:hover {
    background-color: #0097a7;
  }

  .btn-danger {
    background-color: #dc3545;
    border: none;
  }

  .btn-danger:hover {
    background-color: #b52b37;
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

<!-- <div class="overlay"></div>
<header>
  Laundry Admin Dashboard
</header> -->



</head>
<body>
  <!-- Navbar -->
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
      <a href="../admin/brand.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm me-2" style="color: #007bff;">Brand</a>
      <a href="../admin/product.php" class="btn btn-light btn-sm px-3 fw-semibold shadow-sm" style="color: #007bff;">Add Product</a>
    </div>
  </div>
</nav>


  <!-- Page Content -->
  <div class="container my-5">
    <div class="row g-4">
      
      <!-- Add Category Form -->
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-3">Add Category</h5>
            <form id="addCategoryForm">
              <div class="mb-3">
                <label for="cat_name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="cat_name" name="cat_name" placeholder="Enter category name" required>
              </div>
              <button class="btn btn-primary w-100" type="submit">Add Category</button>
              <div id="addMsg" class="mt-3"></div>
            </form>
          </div>
        </div>
      </div>

      <!-- Category List -->
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body" id="categoriesTableWrap">
            <h5 class="card-title mb-3">Your Categories</h5>
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="categoryTable">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form id="editCategoryForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_cat_id" name="cat_id">
          <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-control" id="edit_cat_name" name="cat_name" required>
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


  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../js/category.js"></script> 
</body>
</html>
