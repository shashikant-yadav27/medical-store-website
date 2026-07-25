<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Fetch Featured Products
$featuredProducts = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN product_categories c ON p.category_id = c.id WHERE p.is_featured = 1 AND p.status = 1 LIMIT 8")->fetchAll();

// Fetch New Arrivals
$newArrivals = $pdo->query("SELECT p.*, c.name as category_name FROM products p JOIN product_categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.id DESC LIMIT 4")->fetchAll();

// Fetch Active Categories
$categories = $pdo->query("SELECT * FROM product_categories WHERE status = 1 LIMIT 6")->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-success mb-2 px-3 py-2 rounded-pill">100% Genuine Medicines</span>
                <h1 class="display-4 fw-bold text-dark mb-4">Your Health is Our <span class="text-primary">Priority</span></h1>
                <p class="lead text-muted mb-4">Get your prescription and over-the-counter medicines delivered to your doorstep quickly and safely.</p>
                <div class="d-flex gap-3">
                    <a href="shop.php" class="btn btn-primary btn-lg px-4 shadow-sm">Shop Now</a>
                    <a href="prescription.php" class="btn btn-outline-success btn-lg px-4 bg-white">Upload Prescription</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0 text-center">
                <img src="https://via.placeholder.com/600x400/e8f5e9/2e7d32?text=Medical+Store+Hero+Image" alt="Hero Image" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Features Banner -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <i class="bi bi-truck fs-1 text-primary mb-2"></i>
                <h6 class="fw-bold mb-0">Free Delivery</h6>
                <small class="text-muted">Orders over $50</small>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-shield-check fs-1 text-success mb-2"></i>
                <h6 class="fw-bold mb-0">Secure Payment</h6>
                <small class="text-muted">100% Protected</small>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-capsule fs-1 text-warning mb-2"></i>
                <h6 class="fw-bold mb-0">Genuine Products</h6>
                <small class="text-muted">Quality Guaranteed</small>
            </div>
            <div class="col-md-3 col-6">
                <i class="bi bi-headset fs-1 text-info mb-2"></i>
                <h6 class="fw-bold mb-0">24/7 Support</h6>
                <small class="text-muted">Dedicated Helpline</small>
            </div>
        </div>
    </div>
</section>

<!-- Browse by Category -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0">Browse Categories</h3>
            <a href="shop.php" class="text-decoration-none">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach($categories as $cat): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="shop.php?category=<?php echo $cat['slug']; ?>" class="text-decoration-none">
                    <div class="card category-card text-center py-4 h-100">
                        <div class="card-body">
                            <i class="bi bi-bag-plus fs-1 text-success mb-3 d-block"></i>
                            <h6 class="text-dark fw-bold mb-0"><?php echo htmlspecialchars($cat['name']); ?></h6>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5 bg-white">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">Featured Products</h3>
        <div class="row g-4">
            <?php if(empty($featuredProducts)): ?>
                <div class="col-12 text-center text-muted">No featured products found.</div>
            <?php else: ?>
                <?php foreach($featuredProducts as $prod): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
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
                                <button class="btn btn-outline-primary w-100 add-to-cart-btn" data-id="<?php echo $prod['id']; ?>">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="py-5">
    <div class="container">
        <div class="row rounded-4 overflow-hidden shadow" style="background: linear-gradient(135deg, #198754, #0d6efd);">
            <div class="col-md-7 p-5 text-white d-flex flex-column justify-content-center">
                <h2 class="fw-bold mb-3">Get 20% Off on First Order</h2>
                <p class="lead mb-4">Use code <strong>WELCOME20</strong> at checkout and save big on your medicines and health supplements.</p>
                <div>
                    <a href="shop.php" class="btn btn-light btn-lg fw-bold text-success px-4">Shop Now</a>
                </div>
            </div>
            <div class="col-md-5 d-none d-md-block p-0">
                <img src="https://via.placeholder.com/500x300/ffffff/198754?text=Promo+Image" alt="Promo" class="img-fluid h-100 w-100 object-fit-cover">
            </div>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="py-5 bg-white mb-5">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">New Arrivals</h3>
        <div class="row g-4 justify-content-center">
            <?php if(empty($newArrivals)): ?>
                <div class="col-12 text-center text-muted">No new arrivals found.</div>
            <?php else: ?>
                <?php foreach($newArrivals as $prod): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card product-card h-100 shadow-sm border-0 bg-light">
                        <div class="card-body text-center">
                            <?php if($prod['image']): ?>
                                <img src="<?php echo SITE_URL; ?>assets/uploads/products/<?php echo $prod['image']; ?>" class="img-fluid mb-3 rounded" style="max-height: 150px;" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150x150?text=No+Image" class="img-fluid mb-3 rounded" alt="Placeholder">
                            <?php endif; ?>
                            <h6 class="card-title fw-bold">
                                <a href="product.php?slug=<?php echo $prod['slug']; ?>" class="text-dark text-decoration-none">
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                </a>
                            </h6>
                            <div class="text-success fw-bold fs-5 mb-3">
                                <?php echo formatCurrency($prod['discount_price'] ?: $prod['price']); ?>
                            </div>
                            <button class="btn btn-sm btn-primary w-100 add-to-cart-btn" data-id="<?php echo $prod['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
