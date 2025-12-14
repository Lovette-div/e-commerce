<?php
require_once("../settings/core.php");
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 1) {
    header("Location: ../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <script src="../js/category.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <h1>Category Management</h1>

    <!-- Add Category Form -->
    <form id="addCategoryForm">
        <input type="text" name="name" placeholder="Enter category name" required>
        <button type="submit">Add</button>
    </form>

    <!-- Categories Table -->
    <table border="1" id="categoryTable">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
     <!-- Bootstrap 5.3 JS Bundle -->
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
