<?php
// product.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';

if (empty($slug)) {
    redirect('shop.php');
}

// Fetch Product Details
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, b.name as brand_name 
                       FROM products p 
                       JOIN product_categories c ON p.category_id = c.id 
                       LEFT JOIN brands b ON p.brand_id = b.id 
                       WHERE p.slug = ? AND p.status = 1");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    redirect('shop.php');
}

// Fetch Related Products
$stmtRelated = $pdo->prepare("SELECT p.*, c.name as category_name 
                              FROM products p 
                              JOIN product_categories c ON p.category_id = c.id 
                              WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
                              LIMIT 4");
$stmtRelated->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmtRelated->fetchAll();

include 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom mb-5">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>shop.php" class="text-decoration-none">Shop</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>shop.php?category=<?php echo generateSlug($product['category_name']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row bg-white rounded-4 shadow-sm p-4 mb-5">
        <!-- Product Image -->
        <div class="col-md-5 mb-4 mb-md-0 text-center">
            <?php if($product['image']): ?>
                <img src="<?php echo SITE_URL; ?>assets/uploads/products/<?php echo $product['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/500x500?text=No+Image" class="img-fluid rounded" alt="Placeholder">
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-7 ps-md-5">
            <?php if($product['prescription_required']): ?>
                <span class="badge bg-warning text-dark mb-2 px-3 py-2"><i class="bi bi-file-medical"></i> Prescription Required</span>
            <?php endif; ?>
            
            <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h2>
            
            <?php if($product['generic_name']): ?>
                <p class="text-muted fst-italic mb-3">Generic: <?php echo htmlspecialchars($product['generic_name']); ?></p>
            <?php endif; ?>
            
            <div class="d-flex align-items-center mb-4">
                <div class="me-4">
                    <span class="text-muted small">Brand:</span>
                    <span class="fw-semibold text-primary"><?php echo $product['brand_name'] ? htmlspecialchars($product['brand_name']) : 'Generic'; ?></span>
                </div>
                <div>
                    <span class="text-muted small">Category:</span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($product['category_name']); ?></span>
                </div>
            </div>
            
            <div class="mb-4">
                <?php if($product['discount_price']): ?>
                    <h3 class="text-danger fw-bold d-inline-block me-2 mb-0"><?php echo formatCurrency($product['discount_price']); ?></h3>
                    <span class="text-muted text-decoration-line-through fs-5"><?php echo formatCurrency($product['price']); ?></span>
                    <?php 
                        $discount_pct = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                    ?>
                    <span class="badge bg-success ms-2"><?php echo $discount_pct; ?>% OFF</span>
                <?php else: ?>
                    <h3 class="fw-bold text-dark mb-0"><?php echo formatCurrency($product['price']); ?></h3>
                <?php endif; ?>
                <p class="text-muted small mt-1">Inclusive of all taxes</p>
            </div>
            
            <div class="bg-light p-3 rounded mb-4 border">
                <div class="row align-items-center">
                    <div class="col-sm-6 mb-3 mb-sm-0">
                        <label class="form-label fw-semibold">Quantity</label>
                        <div class="input-group" style="width: 140px;">
                            <button class="btn btn-outline-secondary px-3 qty-btn" type="button" data-action="minus">-</button>
                            <input type="text" class="form-control text-center" id="qtyInput" value="1" readonly>
                            <button class="btn btn-outline-secondary px-3 qty-btn" type="button" data-action="plus">+</button>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <span class="fw-semibold <?php echo ($product['stock_quantity'] > 0) ? 'text-success' : 'text-danger'; ?>">
                            <?php if($product['stock_quantity'] > 0): ?>
                                <i class="bi bi-check-circle-fill"></i> In Stock (<?php echo $product['stock_quantity']; ?> left)
                            <?php else: ?>
                                <i class="bi bi-x-circle-fill"></i> Out of Stock
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex gap-3 mb-4">
                <?php if($product['stock_quantity'] > 0): ?>
                    <button class="btn btn-primary btn-lg flex-grow-1 add-to-cart-btn" data-id="<?php echo $product['id']; ?>" data-quantity="1" id="addToCartMainBtn">
                        <i class="bi bi-cart-plus me-2"></i> Add to Cart
                    </button>
                    <button class="btn btn-success btn-lg flex-grow-1">
                        <i class="bi bi-lightning-charge me-2"></i> Buy Now
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Product Description Tabs -->
    <div class="row mb-5">
        <div class="col-12">
            <ul class="nav nav-tabs border-bottom-0 mb-3" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold text-dark rounded-top" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
                </li>
                <?php if($product['dosage_info']): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold text-dark rounded-top" id="dosage-tab" data-bs-toggle="tab" data-bs-target="#dosage" type="button" role="tab">Dosage & Usage</button>
                </li>
                <?php endif; ?>
                <?php if($product['side_effects']): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold text-dark rounded-top" id="effects-tab" data-bs-toggle="tab" data-bs-target="#effects" type="button" role="tab">Side Effects</button>
                </li>
                <?php endif; ?>
                <?php if($product['storage_instructions']): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold text-dark rounded-top" id="storage-tab" data-bs-toggle="tab" data-bs-target="#storage" type="button" role="tab">Storage</button>
                </li>
                <?php endif; ?>
            </ul>
            <div class="tab-content bg-white p-4 border rounded shadow-sm" id="productTabsContent">
                <div class="tab-pane fade show active" id="desc" role="tabpanel">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?>
                </div>
                <?php if($product['dosage_info']): ?>
                <div class="tab-pane fade" id="dosage" role="tabpanel">
                    <?php echo nl2br(htmlspecialchars($product['dosage_info'])); ?>
                </div>
                <?php endif; ?>
                <?php if($product['side_effects']): ?>
                <div class="tab-pane fade" id="effects" role="tabpanel">
                    <?php echo nl2br(htmlspecialchars($product['side_effects'])); ?>
                </div>
                <?php endif; ?>
                <?php if($product['storage_instructions']): ?>
                <div class="tab-pane fade" id="storage" role="tabpanel">
                    <?php echo nl2br(htmlspecialchars($product['storage_instructions'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if(!empty($relatedProducts)): ?>
    <div class="mt-5">
        <h4 class="fw-bold mb-4">Related Products</h4>
        <div class="row g-4">
            <?php foreach($relatedProducts as $relProd): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100 shadow-sm border-0">
                    <?php if($relProd['image']): ?>
                        <img src="<?php echo SITE_URL; ?>assets/uploads/products/<?php echo $relProd['image']; ?>" class="card-img-top p-3" alt="<?php echo htmlspecialchars($relProd['name']); ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300x300?text=No+Image" class="card-img-top p-3" alt="Placeholder">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title fw-bold mb-2">
                            <a href="product.php?slug=<?php echo $relProd['slug']; ?>" class="text-dark text-decoration-none">
                                <?php echo htmlspecialchars($relProd['name']); ?>
                            </a>
                        </h6>
                        <div class="mt-auto">
                            <div class="mb-2">
                                <span class="product-price text-success"><?php echo formatCurrency($relProd['discount_price'] ?: $relProd['price']); ?></span>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100 add-to-cart-btn" data-id="<?php echo $relProd['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyBtns = document.querySelectorAll('.qty-btn');
    const qtyInput = document.getElementById('qtyInput');
    const addToCartMainBtn = document.getElementById('addToCartMainBtn');
    
    qtyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            let currentQty = parseInt(qtyInput.value);
            if (this.dataset.action === 'plus') {
                if (currentQty < <?php echo $product['stock_quantity']; ?>) {
                    qtyInput.value = currentQty + 1;
                }
            } else {
                if (currentQty > 1) {
                    qtyInput.value = currentQty - 1;
                }
            }
            if(addToCartMainBtn) {
                addToCartMainBtn.dataset.quantity = qtyInput.value;
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
