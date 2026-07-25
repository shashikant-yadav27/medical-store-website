<?php
// shop.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Filters
$category_slug = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$brand_slug = isset($_GET['brand']) ? sanitizeInput($_GET['brand']) : '';
$search_query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : '';

// Build Query
$sql = "SELECT p.*, c.name as category_name FROM products p JOIN product_categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 1";
$params = [];

if ($category_slug) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
}

if ($brand_slug) {
    $sql .= " AND b.slug = ?";
    $params[] = $brand_slug;
}

if ($search_query) {
    $sql .= " AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Sorting
if ($sort === 'price_low') {
    $sql .= " ORDER BY COALESCE(p.discount_price, p.price) ASC";
} elseif ($sort === 'price_high') {
    $sql .= " ORDER BY COALESCE(p.discount_price, p.price) DESC";
} elseif ($sort === 'newest') {
    $sql .= " ORDER BY p.id DESC";
} else {
    $sql .= " ORDER BY p.id DESC"; // Default
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch Categories for Sidebar
$categories = $pdo->query("SELECT * FROM product_categories WHERE status = 1")->fetchAll();
// Fetch Brands for Sidebar
$brands = $pdo->query("SELECT * FROM brands WHERE status = 1")->fetchAll();

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Categories</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <a href="shop.php" class="list-group-item list-group-item-action <?php echo empty($category_slug) ? 'active' : ''; ?>">All Categories</a>
                        <?php foreach($categories as $cat): ?>
                            <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo ($category_slug == $cat['slug']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Brands</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach($brands as $brand): ?>
                            <a href="shop.php?brand=<?php echo $brand['slug']; ?>" class="list-group-item list-group-item-action <?php echo ($brand_slug == $brand['slug']) ? 'fw-bold text-primary' : ''; ?>">
                                <?php echo htmlspecialchars($brand['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h4 class="mb-0 fw-bold">
                    <?php 
                        if($search_query) echo 'Search Results for "'.htmlspecialchars($search_query).'"';
                        elseif($category_slug) echo 'Category Products';
                        else echo 'All Products';
                    ?> 
                    <small class="text-muted fs-6">(<?php echo count($products); ?> found)</small>
                </h4>
                
                <form class="d-flex align-items-center" action="shop.php" method="GET">
                    <?php if($category_slug): ?><input type="hidden" name="category" value="<?php echo $category_slug; ?>"><?php endif; ?>
                    <?php if($search_query): ?><input type="hidden" name="q" value="<?php echo $search_query; ?>"><?php endif; ?>
                    <label class="me-2 text-nowrap fw-semibold">Sort By:</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                        <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>
            
            <div class="row g-4">
                <?php if(empty($products)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-emoji-frown fs-1 text-muted mb-3 d-block"></i>
                        <h5>No products found.</h5>
                        <p class="text-muted">Try adjusting your filters or search criteria.</p>
                        <a href="shop.php" class="btn btn-outline-primary">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $prod): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card product-card h-100 shadow-sm">
                            <?php if($prod['prescription_required']): ?>
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2"><i class="bi bi-file-medical"></i> Rx</span>
                            <?php endif; ?>
                            
                            <?php if($prod['image']): ?>
                                <img src="<?php echo SITE_URL; ?>assets/uploads/products/<?php echo $prod['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=No+Image" class="card-img-top" alt="Placeholder">
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <small class="text-muted mb-1"><?php echo htmlspecialchars($prod['category_name']); ?></small>
                                <h6 class="card-title fw-bold mb-2">
                                    <a href="product.php?slug=<?php echo $prod['slug']; ?>" class="text-dark text-decoration-none">
                                        <?php echo htmlspecialchars($prod['name']); ?>
                                    </a>
                                </h6>
                                <div class="mt-auto">
                                    <div class="mb-2">
                                        <?php if($prod['discount_price']): ?>
                                            <span class="discount-price"><?php echo formatCurrency($prod['discount_price']); ?></span>
                                            <span class="original-price"><?php echo formatCurrency($prod['price']); ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?php echo formatCurrency($prod['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($prod['stock_quantity'] > 0): ?>
                                        <button class="btn btn-outline-primary w-100 add-to-cart-btn" data-id="<?php echo $prod['id']; ?>">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination could go here -->
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
