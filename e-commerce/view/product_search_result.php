<?php
require_once('../settings/core.php');
require_once('../controllers/product_controller.php');
require_once('../controllers/category_controller.php');
require_once('../controllers/brand_controller.php');
require_once('../classes/product_class.php');

// Get filters from URL
$filters = [
    'query' => isset($_GET['query']) ? trim($_GET['query']) : '',
    'category' => isset($_GET['category']) ? intval($_GET['category']) : 0,
    'brand' => isset($_GET['brand']) ? intval($_GET['brand']) : 0,
    'min_price' => isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0,
    'max_price' => isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0
];

$products = advanced_search_ctr($filters);

// Get categories and brands for filters
$categories = get_all_categories_ctr();
$brands = get_all_brands_ctr();

// Pagination
$products_per_page = 12;
$total_products = count($products);
$total_pages = ceil($total_products / $products_per_page);
$current_page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;
$paginated_products = array_slice($products, $offset, $products_per_page);

// Logged-in customer
$customer_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - ShopNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --emerald: #50c8aeff;
            --emerald-dark: #067e74ff;
        }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; }
        .sidebar { min-height: 100vh; background: var(--emerald); color: white; padding-top:20px;}
        .sidebar a { color:white; display:block; padding:12px 20px; border-radius:8px; margin-bottom:5px; text-decoration:none; transition:background 0.3s;}
        .sidebar a:hover, .sidebar a.active { background: var(--emerald-dark);}
        .product-card { background:white; border-radius:15px; overflow:hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 2px 10px rgba(0,0,0,0.08);}
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15);}
        .product-image { height: 250px; object-fit: cover; width: 100%; }
        .product-price { font-weight:700; color: var(--emerald-dark); font-size:1.2rem;}
        .filter-section select, .filter-section input { margin-bottom: 15px; }
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
        <div class="col-lg-10 p-4">
            <div class="welcome-container mb-3" style="background:white; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <h3>Search Results for "<?php echo htmlspecialchars($filters['query']); ?>"</h3>
                <p><?php echo $total_products; ?> product(s) found.</p>
            </div>

            <div class="row">
                <!-- Filters -->
                <div class="col-md-3">
                    <div class="card p-3 mb-4">
                        <h5 class="mb-3"><i class="fas fa-filter"></i> Filters</h5>
                        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Search products..." value="<?php echo htmlspecialchars($filters['query']); ?>">
                        <select id="categoryFilter" class="form-select mb-2">
                            <option value="">All Categories</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['cat_id']; ?>" <?php if($filters['category']==$cat['cat_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cat['cat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select id="brandFilter" class="form-select mb-2">
                            <option value="">All Brands</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>" <?php if($filters['brand']==$brand['brand_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" id="minPrice" class="form-control mb-2" placeholder="Min Price" value="<?php echo $filters['min_price'] ?: ''; ?>">
                        <input type="number" id="maxPrice" class="form-control mb-2" placeholder="Max Price" value="<?php echo $filters['max_price'] ?: ''; ?>">
                        <button id="applyFilters" class="btn btn-success w-100">Apply Filters</button>
                        <button id="clearFilters" class="btn btn-outline-secondary w-100 mt-2">Clear All</button>
                    </div>
                </div>

                <!-- Products -->
                <div class="col-md-9">
                    <div class="row" id="productsContainer">
                        <?php if($paginated_products && count($paginated_products)>0): ?>
                            <?php foreach($paginated_products as $product): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="product-card">
                                        <?php if(!empty($product['product_image'])): ?>
                                            <img src="../<?php echo htmlspecialchars($product['product_image']); ?>" class="product-image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/250x250?text=No+Image" class="product-image">
                                        <?php endif; ?>
                                        <div class="p-3">
                                            <h6><?php echo htmlspecialchars($product['product_title']); ?></h6>
                                            <p class="product-price">$<?php echo number_format($product['product_price'],2); ?></p>
                                            <p><small><?php echo htmlspecialchars($product['cat_name']); ?> | <?php echo htmlspecialchars($product['brand_name']); ?></small></p>
                                            <a href="single_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No products match your criteria.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if($total_pages>1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php if($current_page>1): ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$current_page-1])); ?>">&laquo;</a></li>
                                <?php endif; ?>
                                <?php for($i=1;$i<=$total_pages;$i++): ?>
                                    <li class="page-item <?php echo $i==$current_page?'active':''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if($current_page<$total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$current_page+1])); ?>">&raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('applyFilters').addEventListener('click',()=>{
        const query=document.getElementById('searchInput').value.trim();
        const category=document.getElementById('categoryFilter').value;
        const brand=document.getElementById('brandFilter').value;
        const minPrice=document.getElementById('minPrice').value;
        const maxPrice=document.getElementById('maxPrice').value;

        const params=new URLSearchParams();
        if(query) params.append('query',query);
        if(category) params.append('category',category);
        if(brand) params.append('brand',brand);
        if(minPrice) params.append('min_price',minPrice);
        if(maxPrice) params.append('max_price',maxPrice);

        window.location.href=`product_search_result.php?${params.toString()}`;
    });
    document.getElementById('clearFilters').addEventListener('click',()=>window.location.href='all_product.php');
</script>
</body>
</html>
