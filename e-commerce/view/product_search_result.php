<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');
require_once('../classes/product_class.php');

// Get search parameters
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$brand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

// Fetch all products and filter
$productController = new ProductController();
$all_products = $productController->fetch_all_ctr();

// Apply filters
$filtered_products = array_filter($all_products, function($product) use ($query, $category, $brand, $min_price, $max_price) {
    // Search by title or keywords
    if (!empty($query)) {
        $search_in = strtolower($product['title'] . ' ' . ($product['keywords'] ?? ''));
        if (strpos($search_in, strtolower($query)) === false) {
            return false;
        }
    }
    
    // Filter by category
    if ($category > 0 && $product['cat_id'] != $category) {
        return false;
    }
    
    // Filter by brand
    if ($brand > 0 && $product['brand_id'] != $brand) {
        return false;
    }
    
    // Filter by price range
    if ($min_price > 0 && $product['price'] < $min_price) {
        return false;
    }
    
    if ($max_price > 0 && $product['price'] > $max_price) {
        return false;
    }
    
    return true;
});

// Fetch images for filtered products
$productClass = new Product();
$product_images_map = [];
foreach($filtered_products as $product) {
    $images = $productClass->getImages($product['product_id']);
    if($images && count($images) > 0) {
        $product_images_map[$product['product_id']] = $images[0]['file_path'];
    }
}

// Get categories and brands
$categories = get_all_categories_ctr();
$brandController = new BrandController();
$brands = $brandController->fetch_all_ctr();

// Pagination
$results_per_page = 10;
$total_results = count($filtered_products);
$total_pages = ceil($total_results / $results_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $results_per_page;
$paginated_results = array_slice($filtered_products, $offset, $results_per_page);

$customer_name = $_SESSION['customer_name'] ?? 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Launder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --emerald: #50c8aeff;
            --emerald-dark: #3a9682ff;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
        }
        .sidebar {
            min-height: 100vh;
            background: var(--emerald);
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 16.666667%;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: var(--emerald-dark);
        }
        .main-content {
            margin-left: 16.666667%;
        }
        .search-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .product-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .product-price {
            font-weight: 700;
            color: var(--emerald);
            font-size: 1.3rem;
        }
        .badge-custom {
            background: var(--emerald);
            color: white;
        }
        .filter-badge {
            background: var(--emerald);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .filter-badge .remove {
            cursor: pointer;
            margin-left: 8px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 sidebar d-flex flex-column">
            <h4 class="text-center mb-4"><i class="fas fa-shopping-bag"></i> Launder</h4>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="all_product.php"><i class="fas fa-box-open"></i> All Services</a>
            <a href="#"><i class="fas fa-shopping-cart"></i> Cart</a>
            <a href="#"><i class="fas fa-clipboard-list"></i> Orders</a>
            <a href="#"><i class="fas fa-truck"></i> Track Orders</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Main content -->
        <div class="col-lg-10 main-content p-4">
            <!-- Search Header -->
            <div class="search-header">
                <h3><i class="fas fa-search"></i> Search Results</h3>
                <p class="text-muted mb-3">Found <?php echo $total_results; ?> result(s)</p>

                <!-- Active Filters -->
                <?php if(!empty($query) || $category > 0 || $brand > 0 || $min_price > 0 || $max_price > 0): ?>
                    <div class="mb-3">
                        <strong>Active Filters:</strong><br>
                        <?php if(!empty($query)): ?>
                            <span class="filter-badge">
                                Search: "<?php echo htmlspecialchars($query); ?>"
                                <span class="remove" onclick="removeFilter('query')">&times;</span>
                            </span>
                        <?php endif; ?>

                        <?php if($category > 0): 
                            $cat_name_filter = '';
                            foreach($categories as $cat) {
                                if($cat['cat_id'] == $category) {
                                    $cat_name_filter = $cat['cat_name'];
                                    break;
                                }
                            }
                        ?>
                            <span class="filter-badge">
                                Category: <?php echo htmlspecialchars($cat_name_filter); ?>
                                <span class="remove" onclick="removeFilter('category')">&times;</span>
                            </span>
                        <?php endif; ?>

                        <?php if($brand > 0):
                            $brand_name_filter = '';
                            foreach($brands as $b) {
                                if($b['brand_id'] == $brand) {
                                    $brand_name_filter = $b['brand_name'];
                                    break;
                                }
                            }
                        ?>
                            <span class="filter-badge">
                                Brand: <?php echo htmlspecialchars($brand_name_filter); ?>
                                <span class="remove" onclick="removeFilter('brand')">&times;</span>
                            </span>
                        <?php endif; ?>

                        <?php if($min_price > 0 || $max_price > 0): ?>
                            <span class="filter-badge">
                                Price: GHS<?php echo $min_price > 0 ? number_format($min_price, 2) : '0'; ?> - $<?php echo $max_price > 0 ? number_format($max_price, 2) : '∞'; ?>
                                <span class="remove" onclick="removeFilter('price')">&times;</span>
                            </span>
                        <?php endif; ?>

                        <a href="all_product.php" class="btn btn-sm btn-outline-secondary ms-2">Clear All</a>
                    </div>
                <?php endif; ?>

                <!-- Refine Search -->
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" id="refineSearch" class="form-control" placeholder="Refine search..." value="<?php echo htmlspecialchars($query); ?>">
                    </div>
                    <div class="col-md-3">
                        <select id="refineCategory" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php echo $category == $cat['cat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="refineBrand" class="form-select">
                            <option value="">All Brands</option>
                            <?php foreach($brands as $b): ?>
                                <option value="<?php echo $b['brand_id']; ?>" <?php echo $brand == $b['brand_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($b['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success w-100" onclick="refineSearch()">
                            <i class="fas fa-filter"></i> Refine
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="row">
                <?php if(count($paginated_results) > 0): ?>
                    <?php foreach($paginated_results as $product): ?>
                        <?php
                        // Get category and brand names
                        $cat_name = 'Unknown';
                        foreach($categories as $cat) {
                            if($cat['cat_id'] == $product['cat_id']) {
                                $cat_name = $cat['cat_name'];
                                break;
                            }
                        }

                        $brand_name = 'Unknown';
                        foreach($brands as $b) {
                            if($b['brand_id'] == $product['brand_id']) {
                                $brand_name = $b['brand_name'];
                                break;
                            }
                        }

                        $image_path = $product_images_map[$product['product_id']] ?? null;
                        ?>
                        <div class="col-12">
                            <div class="product-card">
                                <div class="row g-0">
                                    <div class="col-md-2 d-flex align-items-center justify-content-center p-3">
                                        <?php if($image_path): ?>
                                            <img src="/~lovette.philips/<?php echo htmlspecialchars($image_path); ?>" 
                                                 class="product-image rounded" 
                                                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                                                 onerror="this.src='https://placehold.co/150x150?text=No+Image'">
                                        <?php else: ?>
                                            <img src="https://placehold.co/150x150?text=No+Image" class="product-image rounded" alt="No image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-7 p-3">
                                        <h5 class="mb-2"><?php echo htmlspecialchars($product['title']); ?></h5>
                                        <p class="text-muted small mb-2">
                                            <span class="badge badge-custom me-1"><?php echo htmlspecialchars($cat_name); ?></span>
                                            <span class="badge badge-custom"><?php echo htmlspecialchars($brand_name); ?></span>
                                        </p>
                                        <p class="text-muted small mb-1">Product ID: #<?php echo $product['product_id']; ?></p>
                                        <p class="mb-0"><?php echo htmlspecialchars(substr($product['description'], 0, 150)); ?>...</p>
                                    </div>
                                    <div class="col-md-3 p-3 d-flex flex-column align-items-end justify-content-center">
                                        <p class="product-price mb-3">GHS<?php echo number_format($product['price'], 2); ?></p>
                                        <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-success btn-sm mb-2 w-100">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <button class="btn btn-outline-success btn-sm w-100" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-search"></i> No products found matching your criteria. Try adjusting your filters.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page-1])); ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <?php for($i=1; $i<=$total_pages; $i++): ?>
                            <li class="page-item <?php echo $i==$current_page?'active':''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page+1])); ?>">&raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function refineSearch() {
        const query = document.getElementById('refineSearch').value.trim();
        const category = document.getElementById('refineCategory').value;
        const brand = document.getElementById('refineBrand').value;

        const params = new URLSearchParams(window.location.search);
        
        if(query) params.set('query', query);
        else params.delete('query');
        
        if(category) params.set('category', category);
        else params.delete('category');
        
        if(brand) params.set('brand', brand);
        else params.delete('brand');
        
        params.delete('page'); // Reset to page 1
        
        window.location.href = `product_search_result.php?${params.toString()}`;
    }

    function removeFilter(filterType) {
        const params = new URLSearchParams(window.location.search);
        
        if(filterType === 'price') {
            params.delete('min_price');
            params.delete('max_price');
        } else {
            params.delete(filterType);
        }
        
        params.delete('page');
        
        if(params.toString()) {
            window.location.href = `product_search_result.php?${params.toString()}`;
        } else {
            window.location.href = 'all_product.php';
        }
    }

    function addToCart(productId) {
        alert('Add to cart functionality will be implemented soon!\nProduct ID: ' + productId);
    }
</script>
</body>
</html>